<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->string('total_qty')->nullable();
            $table->string('initial_qty')->nullable();
            $table->string('date')->nullable();
            $table->string('re_order')->nullable();
            $table->string('inventory_asset')->nullable();
            $table->string('description', 1000)->nullable();
            $table->string('sale_price')->nullable();
            $table->string('income_account')->nullable();
            $table->string('purchase_info')->nullable();
            $table->string('cost')->nullable();
            $table->string('expense_account')->nullable();
            $table->string('image')->nullable();
            $table->string('temp_qty')->nullable();
            $table->string('sea_transit')->nullable();
            $table->string('air_transit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
