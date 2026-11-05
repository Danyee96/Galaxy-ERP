<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackingStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_histories', function (Blueprint $table) {
            $table->tinyInteger('packing_status')->default(0)->comment('0: Not Packed, 1: Packed')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_histories', function (Blueprint $table) {
            $table->dropColumn('packing_status');
        });
    }
}
