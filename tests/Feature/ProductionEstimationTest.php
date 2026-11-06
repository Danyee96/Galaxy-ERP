<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\EstimateValue;
use App\Models\ProductEstimate;
use App\Models\Products;
use App\Models\User;
use App\Services\Estimation\ProductionEstimationPlanner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Dedicated disposable SQLite file; never uses RefreshDatabase or the configured business DB. */
class ProductionEstimationTest extends TestCase
{
    private string $testDatabase;
    private Products $part;
    private Products $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDatabase = tempnam(sys_get_temp_dir(), 'galaxy-estimation-test-');
        config(['database.default' => 'estimation_test', 'database.connections.estimation_test' => ['driver' => 'sqlite', 'database' => $this->testDatabase, 'prefix' => '', 'foreign_key_constraints' => true]]);
        DB::purge('estimation_test');
        $this->assertSame($this->testDatabase, DB::connection()->getDatabaseName());
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        DB::statement('PRAGMA busy_timeout = 5000');
        Schema::create('products', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('sku')->nullable(); $t->string('category')->nullable(); $t->string('type'); foreach (['total_qty','temp_qty','air_transit','sea_transit'] as $c) $t->string($c)->nullable(); $t->integer('status')->default(1); $t->timestamps(); });
        Schema::create('bundles', function (Blueprint $t) { $t->id(); $t->integer('bundle_id'); $t->integer('product_id'); $t->decimal('quantity', 20, 4); $t->integer('status')->default(1); $t->timestamps(); });
        Schema::create('product_estimates', function (Blueprint $t) { $t->id(); $t->string('title')->nullable(); $t->string('number')->nullable(); $t->integer('bundle_id')->nullable(); $t->integer('quantity')->nullable(); $t->integer('status')->default(1); $t->timestamps(); });
        Schema::create('estimate_values', function (Blueprint $t) { $t->id(); $t->integer('estimate_id'); $t->integer('bundle_id'); $t->integer('product_id'); $t->integer('quantity_in_stock'); $t->integer('quantity_needed'); $t->timestamps(); });
        require_once database_path('migrations/2026_11_05_000000_add_multi_system_estimation_planning.php');
        (new \AddMultiSystemEstimationPlanning)->up();
        $this->part = Products::create(['name' => 'Battery', 'sku' => 'BAT', 'type' => 'Inventory', 'total_qty' => '40', 'temp_qty' => '40', 'air_transit' => '0', 'sea_transit' => '0']);
        $this->model = Products::create(['name' => 'System A', 'sku' => 'SYS-A', 'type' => 'Bundle']);
        Bundle::create(['bundle_id' => $this->model->id, 'product_id' => $this->part->id, 'quantity' => 2, 'status' => 1]);
        $this->actingAs((new User)->forceFill(['id' => 99, 'name' => 'Synthetic planner', 'role_id' => 1]));
    }

    protected function tearDown(): void
    {
        DB::disconnect('estimation_test');
        if (isset($this->testDatabase) && is_file($this->testDatabase)) unlink($this->testDatabase);
        parent::tearDown();
    }

    private function input(): array
    {
        return ['title' => 'Synthetic plan', 'plan_date' => '2026-11-05', 'mode' => 'manual', 'models' => [['product_id' => $this->model->id, 'quantity' => 10, 'weight' => 1]], 'request_key' => (string) Str::uuid()];
    }

    private function readyInput(): array
    {
        $input = $this->input();
        $input['preview_fingerprint'] = app(ProductionEstimationPlanner::class)->preview($input)['fingerprint'];
        return $input;
    }

    public function test_preview_and_save_do_not_change_physical_or_projected_stock(): void
    {
        $input = $this->input();
        $preview = $this->postJson(route('productionEstimation.preview'), $input)->assertOk();
        $input['preview_fingerprint'] = $preview->json('plan.fingerprint');
        $response = $this->postJson(route('productionEstimation.store'), $input)->assertCreated();
        $estimate = ProductEstimate::findOrFail($response->json('id'));
        $this->assertCount(1, $estimate->systems);
        $this->assertCount(1, $estimate->components);
        $this->assertSame('20', $estimate->calculation_snapshot['components'][0]['required']);
        $this->assertSame('40', $this->part->fresh()->total_qty);
        $this->assertSame('40', $this->part->fresh()->temp_qty);
        $this->assertSame(0, EstimateValue::count());
    }

    public function test_multiple_models_and_duplicate_recipe_lines_persist_as_one_component(): void
    {
        $b = Products::create(['name' => 'System B', 'type' => 'Bundle']);
        Bundle::create(['bundle_id' => $b->id, 'product_id' => $this->part->id, 'quantity' => 2, 'status' => 1]);
        Bundle::create(['bundle_id' => $b->id, 'product_id' => $this->part->id, 'quantity' => 2, 'status' => 1]);
        $input = $this->input();
        $input['models'][] = ['product_id' => $b->id, 'quantity' => 5, 'weight' => 1];
        $input['preview_fingerprint'] = app(ProductionEstimationPlanner::class)->preview($input)['fingerprint'];
        $estimate = app(ProductionEstimationPlanner::class)->save($input, 99);
        $this->assertCount(2, $estimate->systems);
        $this->assertCount(1, $estimate->components);
        $this->assertSame('40', $estimate->calculation_snapshot['components'][0]['required']);
    }

    public function test_inventory_change_rejects_stale_preview_and_rolls_back(): void
    {
        $input = $this->readyInput();
        $this->part->update(['total_qty' => 39]);
        $this->postJson(route('productionEstimation.store'), $input)->assertStatus(409)->assertJsonStructure(['message', 'plan' => ['fingerprint']]);
        $this->assertSame(0, ProductEstimate::count());
        $this->assertSame(0, DB::table('estimate_number_counters')->count());
    }

    public function test_recipe_change_also_rejects_a_stale_preview(): void
    {
        $input = $this->readyInput();
        Bundle::where('bundle_id', $this->model->id)->update(['quantity' => 3]);
        $this->postJson(route('productionEstimation.store'), $input)->assertStatus(409);
        $this->assertSame(0, ProductEstimate::count());
    }

    public function test_save_is_idempotent_and_rejects_reused_key_with_changed_input(): void
    {
        $input = $this->readyInput();
        $first = $this->postJson(route('productionEstimation.store'), $input)->assertCreated()->json('id');
        $this->assertSame($first, $this->postJson(route('productionEstimation.store'), $input)->assertCreated()->json('id'));
        $input['title'] = 'Changed';
        $this->postJson(route('productionEstimation.store'), $input)->assertStatus(422);
        $this->assertSame(1, ProductEstimate::count());
    }

    public function test_snapshot_details_and_exports_survive_inventory_and_recipe_changes(): void
    {
        $estimate = app(ProductionEstimationPlanner::class)->save($this->readyInput(), 99);
        $original = $estimate->calculation_snapshot;
        $this->part->update(['name' => 'Changed component', 'total_qty' => 0]);
        Bundle::where('bundle_id', $this->model->id)->update(['quantity' => 99]);
        $this->assertSame($original, $estimate->fresh()->calculation_snapshot);
        $this->get(route('productionEstimation.show', $estimate->id))->assertOk()->assertSee('Battery')->assertDontSee('Changed component');
        $this->get(route('SingleEstimateDownload', $estimate->id))->assertOk()->assertSee('Battery');
        $csv = $this->get(route('productionEstimation.csv', $estimate->id))->assertOk()->streamedContent();
        $this->assertStringContainsString('Battery', $csv);
        $this->assertStringNotContainsString('Changed component', $csv);
        $pdf = $this->get(route('productionEstimation.pdf', $estimate->id))->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }

    public function test_legacy_commitments_and_reports_do_not_count_new_plans_twice(): void
    {
        $old = ProductEstimate::create(['number' => '1200', 'title' => 'Legacy', 'bundle_id' => $this->model->id, 'quantity' => 4]);
        EstimateValue::create(['estimate_id' => $old->id, 'bundle_id' => $this->model->id, 'product_id' => $this->part->id, 'quantity_in_stock' => 8, 'quantity_needed' => -2]);
        $this->part->update(['temp_qty' => 32]);
        $input = $this->readyInput();
        $new = app(ProductionEstimationPlanner::class)->save($input, 99);
        $this->assertSame('32', $new->calculation_snapshot['components'][0]['available']);
        $after = app(ProductionEstimationPlanner::class)->preview($input);
        $this->assertSame('32', $after['components'][0]['available']);
        $this->get(route('SingleEstimateDownload', $old->id))->assertOk()->assertSee('Legacy');
        $report = app(\App\Http\Controllers\user_portal\QuickBookController::class)->productEstimatesCalculation()->getData()['inventoryWithEstimates'];
        $this->assertCount(1, $report);
        $this->assertSame(-2, $report->first()['total_needed']);
        $this->assertSame(8, $report->first()['total_in_stock']);
    }

    public function test_legacy_save_keeps_reservation_semantics_and_response_envelope(): void
    {
        ProductEstimate::create(['number' => '1400', 'title' => 'Existing']);
        $response = $this->postJson(route('saveProductEstimates'), ['id' => $this->model->id, 'qty' => 3, 'title' => 'Legacy client', 'estimate_number' => '1'])->assertOk()->assertJsonPath('response.status', 1);
        $this->assertSame('1401', $response->json('response.number'));
        $this->assertSame('34', $this->part->fresh()->temp_qty);
        $this->assertSame('40', $this->part->fresh()->total_qty);
        $this->assertSame(6, EstimateValue::first()->quantity_in_stock);
    }

    public function test_validation_and_role_checks_reject_bad_plans(): void
    {
        $input = $this->input();
        $input['models'][] = $input['models'][0];
        $this->postJson(route('productionEstimation.preview'), $input)->assertStatus(422);
        $input = $this->input(); $input['models'][0]['product_id'] = 99999;
        $this->postJson(route('productionEstimation.preview'), $input)->assertStatus(422);
        $this->actingAs((new User)->forceFill(['id' => 98, 'role_id' => 2]));
        $this->postJson(route('productionEstimation.preview'), $this->input())->assertForbidden();
        $this->assertSame(0, ProductEstimate::count());
    }

    public function test_archiving_a_plan_never_releases_stock(): void
    {
        $estimate = app(ProductionEstimationPlanner::class)->save($this->readyInput(), 99);
        $this->get(route('deleteEstimate', $estimate->id))->assertStatus(405);
        $this->post(route('productionEstimation.archive', $estimate->id))->assertRedirect(route('productEstimates'));
        $this->assertSame(0, $estimate->fresh()->status);
        $this->assertSame('40', $this->part->fresh()->temp_qty);
    }

    public function test_listing_supports_legacy_and_multi_model_plans(): void
    {
        ProductEstimate::create(['title' => 'Historic fixture', 'number' => '1600', 'bundle_id' => $this->model->id, 'quantity' => 2]);
        app(ProductionEstimationPlanner::class)->save($this->readyInput(), 99);
        $this->get(route('productEstimates'))->assertOk()->assertSee('Historic fixture')->assertSee('Synthetic plan');
        $this->get(route('productEstimates', ['kind' => 'planning']))->assertOk()->assertDontSee('Historic fixture')->assertSee('Synthetic plan');
    }

    public function test_new_legacy_commitment_invalidates_a_preview_without_double_counting(): void
    {
        $input = $this->readyInput();
        app(ProductionEstimationPlanner::class)->saveLegacy(['title' => 'Other reservation', 'id' => $this->model->id, 'qty' => 1]);
        $this->postJson(route('productionEstimation.store'), $input)->assertStatus(409)->assertJsonPath('plan.components.0.available', '38');
        $this->assertSame(1, ProductEstimate::count());
        $this->assertSame('40', $this->part->fresh()->total_qty);
    }

    public function test_missing_recipe_and_inactive_inventory_fail_without_creating_a_plan(): void
    {
        $this->part->update(['status' => 0]);
        $this->postJson(route('productionEstimation.preview'), $this->input())->assertStatus(422);
        $this->part->update(['status' => 1]);
        Bundle::where('bundle_id', $this->model->id)->delete();
        $this->postJson(route('productionEstimation.preview'), $this->input())->assertStatus(422);
        $this->assertSame(0, ProductEstimate::count());
    }

    public function test_exports_escape_formula_cells_and_views_escape_stored_markup(): void
    {
        $this->part->update(['name' => ' =SUM(1,2)']);
        $input = $this->readyInput(); $input['title'] = '=2+3';
        $estimate = app(ProductionEstimationPlanner::class)->save($input, 99);
        $csv = $this->get(route('productionEstimation.csv', $estimate->id))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=2+3", $csv);
        $this->assertStringContainsString("' =SUM(1,2)", $csv);
        $this->part->update(['name' => '<img src=x onerror=alert(1)>']);
        $estimate = app(ProductionEstimationPlanner::class)->save($this->readyInput(), 99);
        $this->get(route('productionEstimation.show', $estimate->id))->assertOk()->assertDontSee('<img src=x', false)->assertSee('&lt;img src=x', false);
    }

    public function test_export_controls_and_snapshot_payloads_require_planning_permissions(): void
    {
        $this->get(route('createProductEstimates'))->assertOk()
            ->assertSee('id="plan-export-excel" disabled', false)->assertSee('id="plan-print" disabled', false);
        $estimate = app(ProductionEstimationPlanner::class)->save($this->readyInput(), 99);
        $this->part->update(['total_qty' => '1']);
        $page = $this->get(route('productionEstimation.show', $estimate->id))->assertOk()
            ->assertSee('id="plan-print"', false)->assertSee('id="plan-export-data"', false);
        preg_match('/id="plan-export-data">(.*?)<\/script>/s', $page->getContent(), $matches);
        $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('40', $data['plan']['components'][0]['physical_stock']);
        $this->assertSame('20', $data['plan']['components'][0]['required']);
        $legacy = app(ProductionEstimationPlanner::class)->saveLegacy(['title' => 'Historic export', 'id' => $this->model->id, 'qty' => 2]);
        $this->get(route('SingleEstimateDownload', $legacy->id))->assertOk()
            ->assertSee('id="plan-export-excel"', false)->assertSee('id="plan-print"', false)->assertSee('Not recorded in this historical estimate');
        $this->actingAs((new User)->forceFill(['id' => 98, 'role_id' => 2]));
        $this->get(route('productionEstimation.show', $estimate->id))->assertForbidden();
        $this->get(route('SingleEstimateDownload', $legacy->id))->assertForbidden();
    }

    public function test_concurrent_saves_receive_distinct_numbers_without_deducting_stock(): void
    {
        if (!function_exists('pcntl_fork')) $this->markTestSkipped('pcntl is required for concurrent connection testing.');
        $input = $this->readyInput();
        DB::disconnect('estimation_test');
        $pids = [];
        for ($i = 0; $i < 2; $i++) {
            $pid = pcntl_fork();
            if ($pid === -1) $this->fail('Cannot fork concurrent test.');
            if ($pid === 0) {
                try {
                    DB::reconnect('estimation_test'); DB::statement('PRAGMA busy_timeout = 5000');
                    $input['request_key'] = (string) Str::uuid();
                    app(ProductionEstimationPlanner::class)->save($input, 99);
                    exit(0);
                } catch (\Throwable $e) { fwrite(STDERR, 'Concurrent fixture save failed: '.$e->getMessage()."\n"); exit(1); }
            }
            $pids[] = $pid;
        }
        foreach ($pids as $pid) { pcntl_waitpid($pid, $status); $this->assertSame(0, pcntl_wexitstatus($status)); }
        DB::reconnect('estimation_test');
        $this->assertSame(2, ProductEstimate::count());
        $this->assertSame(2, ProductEstimate::pluck('number')->unique()->count());
        $this->assertSame('40', $this->part->fresh()->total_qty);
        $this->assertSame('40', $this->part->fresh()->temp_qty);
    }
}
