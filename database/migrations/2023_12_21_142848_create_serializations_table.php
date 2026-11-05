<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSerializationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serializations', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('dealer_name')->nullable();
            $table->string('dealer_city')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('delivery_boy')->nullable();
            $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('serializations');
    }
}
