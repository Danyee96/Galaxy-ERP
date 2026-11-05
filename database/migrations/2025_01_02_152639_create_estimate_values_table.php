<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimateValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_values', function (Blueprint $table) {
            $table->id();
            $table->integer('estimate_id')->nullable();
            $table->integer('bundle_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('quantity_in_stock')->nullable();
            $table->integer('quantity_needed')->nullable();
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
        Schema::dropIfExists('estimate_values');
    }
}
