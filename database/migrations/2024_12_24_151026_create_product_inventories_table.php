<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->integer('transit_id')->nullable();
            $table->string('stock_in')->nullable();
            $table->integer('status')->nullable();
            $table->integer('entry_by')->nullable();
            $table->integer('received_by')->nullable();
            $table->date('expected_date')->nullable();
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
        Schema::dropIfExists('product_inventories');
    }
}
