<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->date('entry_date')->nullable();
            $table->integer('error_id')->nullable();
            $table->string('error_description',5000)->nullable();
            $table->string('maker_name')->nullable();
            $table->string('model')->nullable();
            $table->integer('power_id')->nullable();
            $table->integer('status')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('exit_by')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('resolution',5000)->nullable();
            $table->double('cost',11,2)->default(0);
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
        Schema::dropIfExists('item_histories');
    }
}
