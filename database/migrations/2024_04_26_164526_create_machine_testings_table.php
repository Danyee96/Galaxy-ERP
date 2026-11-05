<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineTestingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_testings', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable();
            $table->string('machine_barcode')->nullable();
            $table->string('supply_amp')->nullable();
            $table->string('wifi_light')->nullable();
            $table->string('no_load_voltage')->nullable();
            $table->string('pv_series')->nullable();
            $table->string('ld_inp_series')->nullable();
            $table->string('no_ld_sharing')->nullable();
            $table->string('direct_pv_run')->nullable();
            $table->string('wapda_check')->nullable();
            $table->string('ovl_test_bat')->nullable();
            $table->string('check_on_grid')->nullable();
            $table->string('off_grid')->nullable();
            $table->string('button_test')->nullable();
            $table->string('ck_amp_d')->nullable();
            $table->string('short_circuit')->nullable();
            $table->string('inp_series')->nullable();
            $table->string('agm_chg_amp')->nullable();
            $table->string('fld_chg_amp')->nullable();
            $table->string('dual_out_test')->nullable();
            $table->string('ovl_test_power')->nullable();
            $table->string('inductive_load_test')->nullable();
            $table->string('total_time')->nullable();
            $table->string('test_by')->nullable();
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
        Schema::dropIfExists('machine_testings');
    }
}
