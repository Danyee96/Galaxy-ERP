<?php

namespace Tests\Feature;

use App\Models\ProductInventory;
use App\Models\Products;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransitOverdueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Karachi', 'database.default' => 'transit_test', 'database.connections.transit_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('transit_test');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Carbon::setTestNow(Carbon::parse('2026-11-05 20:30:00', 'UTC')); // November 6 in the application timezone.
        Schema::create('product_inventories', function (Blueprint $t) {
            $t->id(); foreach (['product_id','transit_id','status','entry_by','received_by','remaining_stock','received_stock'] as $c) $t->integer($c)->nullable();
            $t->string('stock_in')->nullable(); $t->date('expected_date')->nullable(); $t->date('production_date')->nullable(); $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('sku')->nullable();
            foreach (['total_qty','temp_qty','air_transit','sea_transit'] as $c) $t->string($c)->default('0'); $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); });
        $this->actingAs((new User)->forceFill(['id' => 99, 'role_id' => 1, 'name' => 'Transit tester']));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); DB::disconnect('transit_test'); parent::tearDown();
    }

    private function shipment(array $attributes = []): ProductInventory
    {
        return ProductInventory::create(array_merge(['transit_id' => 2, 'status' => 0, 'stock_in' => '10', 'received_stock' => 0, 'remaining_stock' => 10, 'expected_date' => '2026-11-01'], $attributes));
    }

    public function test_overdue_uses_local_calendar_days_and_excludes_received_or_undated_shipments(): void
    {
        $late = $this->shipment();
        $yesterday = $this->shipment(['expected_date' => '2026-11-05']);
        foreach ([['expected_date' => '2026-11-06'], ['expected_date' => '2026-11-07'], ['expected_date' => null], ['status' => 1], ['expected_date' => '0000-00-00'], ['transit_id' => 9]] as $attributes) {
            $this->assertSame(0, $this->shipment($attributes)->overdue_days);
        }
        $this->assertSame(5, $late->overdue_days);
        $this->assertSame(1, $yesterday->overdue_days);
        $this->assertSame([$late->id, $yesterday->id], ProductInventory::overdue()->orderBy('id')->pluck('id')->all());
        $late->update(['status' => 1]);
        $this->assertSame(0, $late->overdue_days);
        $this->assertSame(1, ProductInventory::overdue()->count());
    }

    public function test_filters_and_badges_do_not_write_or_duplicate_records(): void
    {
        $air = Products::create(['name' => 'Late air item']);
        $sea = Products::create(['name' => 'Late sea item']);
        $ontime = Products::create(['name' => 'On-time item']);
        $this->shipment(['product_id' => $air->id]);
        $this->shipment(['product_id' => $sea->id, 'transit_id' => 3]);
        $this->shipment(['product_id' => $ontime->id, 'expected_date' => '2026-11-06']);
        $before = ProductInventory::orderBy('id')->get()->toArray();
        for ($i = 0; $i < 2; $i++) {
            $this->get(route('inTransitInventory', ['overdue' => 1, 'transit_method' => 2]))->assertOk()
                ->assertSee('2 overdue shipments')->assertSee('Overdue by 5 days')->assertSee('Late air item')
                ->assertDontSee('Late sea item')->assertDontSee('On-time item')->assertSee('transit-overdue-row');
        }
        $this->assertSame($before, ProductInventory::orderBy('id')->get()->toArray());
        $this->getJson(route('inTransitInventory', ['overdue' => 'invalid']))->assertStatus(422);
        $this->getJson(route('inTransitInventory', ['transit_method' => 99]))->assertStatus(422);
        $this->get(route('inTransitInventory', ['overdue' => 1, 'transit_method' => 1]))->assertOk()->assertSee('No matching in-transit inventory.');
    }

    public function test_partial_receipt_remains_overdue_until_full_receipt_without_changing_existing_stock_math(): void
    {
        $product = Products::create(['name' => 'Battery', 'total_qty' => '0', 'temp_qty' => '10', 'air_transit' => '10', 'sea_transit' => '0']);
        $shipment = $this->shipment(['product_id' => $product->id]);
        $this->post(route('markReceivedTransitNew'), ['product_id' => $shipment->id, 'new_quantity' => 4])->assertRedirect();
        $this->assertSame(5, $shipment->fresh()->overdue_days);
        $this->assertSame(1, ProductInventory::overdue()->count());
        $this->assertSame(6, $shipment->fresh()->remaining_stock);
        $this->post(route('markReceivedTransitNew'), ['product_id' => $shipment->id, 'new_quantity' => 6])->assertRedirect(route('receivedTransitInventory'));
        $this->assertSame(0, ProductInventory::overdue()->count());
        $this->assertSame(0, $shipment->fresh()->overdue_days);
        $this->assertSame('10', $product->fresh()->total_qty);
        $this->assertSame('10', $product->fresh()->temp_qty);
        $this->assertSame('0', $product->fresh()->air_transit);
        $this->get(route('inTransitInventory'))->assertOk()->assertSee('No overdue shipments.')->assertDontSee('Overdue by');
    }
}
