<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinishedGoodsOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finished_goods_outs', function (Blueprint $table) {
            $table->id();
            $table->integer('finished_good_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->date('sale_date')->nullable();
            $table->integer('power_id')->nullable();
            $table->string('model')->nullable();
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
        Schema::dropIfExists('finished_goods_outs');
    }
}
