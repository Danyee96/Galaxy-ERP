<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinishedGoodsInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finished_goods_inventories', function (Blueprint $table) {
            $table->id();
            $table->integer('finished_good_id')->nullable();
            $table->integer('transit_id')->nullable();
            $table->double('stock_in',11,2)->nullable();
            $table->string('remarks',1000)->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('finished_goods_inventories');
    }
}
