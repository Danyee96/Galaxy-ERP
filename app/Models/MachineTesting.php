<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineTesting extends Model
{
    use HasFactory;

    protected $fillable = ['barcode','machine_barcode','supply_amp','wifi_light','no_load_voltage','pv_series','ld_inp_series','no_ld_sharing','direct_pv_run','wapda_check','ovl_test_bat','check_on_grid','off_grid','button_test','ck_amp_d','short_circuit','inp_series','agm_chg_amp','fld_chg_amp','dual_out_test','ovl_test_power','inductive_load_test','total_time','test_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'test_by');
    }
}
