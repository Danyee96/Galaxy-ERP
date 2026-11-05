<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUpsModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('u_p_s_models', function (Blueprint $table) {
            $table->double('semi_qty',11,2)->nullable()->after('temp_qty');
            $table->double('semi_temp_qty',11,2)->nullable()->after('semi_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('u_p_s_models', function (Blueprint $table) {
            //
        });
    }
}
