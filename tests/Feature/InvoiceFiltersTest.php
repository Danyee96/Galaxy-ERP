<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Products;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'invoice_filter_test', 'database.connections.invoice_filter_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('invoice_filter_test');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('invoices', function (Blueprint $t) { $t->id(); $t->string('invoice_number'); $t->string('invoice_name'); $t->integer('total_count'); $t->integer('created_by')->nullable(); $t->timestamps(); });
        Schema::create('invoice_details', function (Blueprint $t) { $t->id(); $t->integer('invoice_id'); $t->integer('product_id'); $t->integer('product_qty'); $t->timestamps(); });
        Schema::create('products', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('sku')->nullable(); $t->timestamps(); });
        $this->actingAs((new User)->forceFill(['id' => 99, 'role_id' => 1, 'name' => 'Invoice tester']));
    }

    protected function tearDown(): void
    {
        DB::disconnect('invoice_filter_test'); parent::tearDown();
    }

    private function invoice(string $number, string $title, int $count, string $date): Invoice
    {
        return tap(Invoice::create(['invoice_number' => $number, 'invoice_name' => $title, 'total_count' => $count]), fn ($invoice) => $invoice->forceFill(['created_at' => $date, 'updated_at' => $date])->save());
    }

    public function test_combined_filters_and_inclusive_dates_return_exact_aggregates(): void
    {
        $this->invoice('1201', 'Solar workshop', 5, '2026-11-01 00:00:00');
        $this->invoice('1202', 'Solar workshop', 7, '2026-11-05 23:59:59');
        $this->invoice('1300', 'Solar workshop', 20, '2026-11-05 12:00:00');
        $this->invoice('1203', 'Other workshop', 30, '2026-11-05 12:00:00');
        $this->invoice('1204', 'Solar workshop', 50, '2026-11-06 00:00:00');
        $response = $this->get(route('getInvoice', ['invoice_number' => '12', 'invoice_name' => 'Solar', 'date_from' => '2026-11-01', 'date_to' => '2026-11-05', 'sort' => 'number', 'direction' => 'asc']))->assertOk();
        $this->assertSame(['1201', '1202'], $response['datas']->pluck('invoice_number')->all());
        $this->assertSame(2, (int) $response['summary']->invoice_count);
        $this->assertSame(12, (int) $response['summary']->item_count);
        $response->assertSee('Not recorded')->assertSee('Apply Filters');
    }

    public function test_product_search_is_combined_with_title_and_pagination_keeps_filters_and_sorting(): void
    {
        $product = Products::create(['name' => 'Control board', 'sku' => 'PCB-X']);
        for ($i = 1; $i <= 27; $i++) {
            $invoice = $this->invoice((string) (1000 + $i), 'Factory', $i, '2026-11-01 12:00:00');
            InvoiceDetails::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'product_qty' => $i]);
        }
        $this->invoice('9999', 'Other', 100, '2026-11-01 12:00:00');
        $filters = ['search' => 'PCB-X', 'invoice_name' => 'Factory', 'sort' => 'quantity', 'direction' => 'asc'];
        $response = $this->get(route('getInvoice', $filters))->assertOk();
        $this->assertSame(27, $response['datas']->total());
        $this->assertSame(378, (int) $response['summary']->item_count);
        $this->assertStringContainsString('invoice_name=Factory', $response['datas']->nextPageUrl());
        $this->assertStringContainsString('sort=quantity', $response['datas']->nextPageUrl());
        $page2 = $this->get(route('getInvoice', $filters + ['page' => 2]))->assertOk();
        $this->assertSame(['1026', '1027'], $page2['datas']->pluck('invoice_number')->all());
    }

    public function test_ajax_and_empty_results_preserve_creation_and_do_not_modify_invoices(): void
    {
        $this->invoice('1500', 'Issuance fixture', 9, '2026-11-01 12:00:00');
        $before = Invoice::get()->toArray();
        $this->get(route('getInvoice', ['invoice_name' => 'missing']))->assertOk()->assertSee('No invoices match these filters.')->assertSee('Apply Filters')->assertSee('id="invoiceModal"', false);
        $fragment = $this->get(route('getInvoice', ['invoice_name' => 'Issuance']), ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])->assertOk();
        $fragment->assertSee('Issuance fixture')->assertDontSee('<html', false)->assertSee('Print');
        $this->assertSame($before, Invoice::get()->toArray());
    }

    public function test_invalid_ranges_sorts_and_unrecorded_financial_filters_are_rejected(): void
    {
        foreach ([['date_from' => '2026-11-10', 'date_to' => '2026-11-01'], ['date_from' => 'invalid'], ['sort' => 'DROP TABLE invoices'], ['direction' => 'unsafe'], ['page' => 0], ['payment_status' => 'paid'], ['amount_min' => 10], ['customer' => 'Alice']] as $filters) {
            $this->getJson(route('getInvoice', $filters))->assertStatus(422);
        }
        $this->get(route('getInvoice'))->assertOk()->assertSee('Apply Filters');
    }
}
