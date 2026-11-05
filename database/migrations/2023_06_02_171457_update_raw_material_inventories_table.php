<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRawMaterialInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_material_inventories', function (Blueprint $table) {
            $table->integer('entry_by')->nullable()->after('status');
            $table->integer('received_by')->nullable()->after('entry_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_material_inventories', function (Blueprint $table) {
            //
        });
    }
}
