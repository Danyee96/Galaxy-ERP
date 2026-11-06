<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateWarehouseInventory extends Migration
{
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id(); $t->string('code', 40)->unique(); $t->string('name'); $t->text('address')->nullable(); $t->text('description')->nullable(); $t->boolean('active')->default(true); $t->timestamps();
        });
        Schema::create('warehouse_locations', function (Blueprint $t) {
            $t->id(); $t->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete(); $t->string('rack', 60)->default(''); $t->string('row', 60); $t->string('column', 60); $t->boolean('active')->default(true); $t->string('kind', 20)->default('physical'); $t->decimal('reorder_level', 24, 4)->default(0); $t->timestamps(); $t->unique(['warehouse_id','rack','row','column'], 'warehouse_location_unique');
        });
        Schema::create('warehouse_items', function (Blueprint $t) {
            $t->id(); $t->string('source_type', 20); $t->unsignedBigInteger('source_id'); $t->timestamps(); $t->unique(['source_type','source_id']);
        });
        Schema::create('warehouse_balances', function (Blueprint $t) {
            $t->id(); $t->foreignId('item_id')->constrained('warehouse_items')->restrictOnDelete(); $t->foreignId('location_id')->constrained('warehouse_locations')->restrictOnDelete(); $t->decimal('quantity', 24, 4)->default(0); $t->timestamps(); $t->unique(['item_id','location_id']);
        });
        Schema::create('warehouse_reorder_levels', function (Blueprint $t) { $t->id(); $t->foreignId('item_id')->constrained('warehouse_items')->restrictOnDelete(); $t->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete(); $t->decimal('quantity',24,4); $t->unique(['item_id','warehouse_id']); });
        Schema::create('warehouse_operations', function (Blueprint $t) {
            $t->id(); $t->uuid('request_key')->unique(); $t->char('input_hash', 64); $t->string('type', 40); $t->string('source',60)->nullable(); $t->string('reference')->nullable(); $t->date('date'); $t->unsignedBigInteger('user_id')->nullable(); $t->text('notes')->nullable(); $t->json('result')->nullable(); $t->timestamps();
        });
        Schema::create('warehouse_movements', function (Blueprint $t) {
            $t->id(); $t->foreignId('operation_id')->constrained('warehouse_operations')->restrictOnDelete(); $t->foreignId('item_id')->constrained('warehouse_items')->restrictOnDelete(); $t->foreignId('source_location_id')->nullable()->constrained('warehouse_locations')->restrictOnDelete(); $t->foreignId('destination_location_id')->nullable()->constrained('warehouse_locations')->restrictOnDelete(); $t->decimal('quantity',24,4); $t->decimal('source_before',24,4)->nullable(); $t->decimal('source_after',24,4)->nullable(); $t->decimal('destination_before',24,4)->nullable(); $t->decimal('destination_after',24,4)->nullable(); $t->timestamps(); $t->index(['item_id','created_at']);
        });
        Schema::create('warehouse_transfers', function (Blueprint $t) {
            $t->id(); $t->foreignId('operation_id')->unique()->constrained('warehouse_operations')->restrictOnDelete(); $t->string('status',30)->default('pending'); $t->timestamps();
        });
        Schema::create('warehouse_transfer_lines', function (Blueprint $t) {
            $t->id(); $t->foreignId('transfer_id')->constrained('warehouse_transfers')->restrictOnDelete(); $t->foreignId('item_id')->constrained('warehouse_items')->restrictOnDelete(); $t->foreignId('source_location_id')->constrained('warehouse_locations')->restrictOnDelete(); $t->foreignId('destination_location_id')->constrained('warehouse_locations')->restrictOnDelete(); $t->decimal('quantity',24,4); $t->decimal('received',24,4)->default(0); $t->timestamps();
        });
        Schema::create('warehouse_transit_receipts', function (Blueprint $t) { $t->id(); $t->foreignId('operation_id')->unique()->constrained('warehouse_operations')->restrictOnDelete(); $t->string('document_type',20); $t->unsignedBigInteger('document_id'); $t->decimal('quantity',24,4); $t->timestamps(); $t->index(['document_type','document_id']); });
        Schema::create('warehouse_state', function (Blueprint $t) { $t->string('key')->primary(); $t->text('value')->nullable(); });
        Schema::create('warehouse_legacy_requests', function (Blueprint $t) { $t->string('key')->primary(); $t->char('input_hash',64); $t->unsignedBigInteger('user_id'); $t->integer('status'); $t->json('headers'); $t->longText('body'); });
    }
    public function down()
    {
        // Refuse rollback after operational use; history must never be silently destroyed.
        if (\Illuminate\Support\Facades\DB::table('warehouse_movements')->exists()) throw new RuntimeException('Warehouse history exists. Export/reconcile before an explicitly managed rollback.');
        foreach (['warehouse_legacy_requests','warehouse_state','warehouse_transit_receipts','warehouse_transfer_lines','warehouse_transfers','warehouse_movements','warehouse_operations','warehouse_reorder_levels','warehouse_balances','warehouse_items','warehouse_locations','warehouses'] as $table) Schema::dropIfExists($table);
    }
}
