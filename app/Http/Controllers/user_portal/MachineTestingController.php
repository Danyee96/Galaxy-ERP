<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\MachineTesting;

class MachineTestingController extends Controller
{
    public function viewTesting()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Testing Form"]
        ];
        return view('user_portal.MachineTesting.testing-form',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveTesting(Request $request)
    {
        $serial_number = $request->serial_number;
        $supply_amp = $request->supply_amp;
        $no_load_voltage = $request->no_load_voltage;
        $wifi_light = $request->wifi_light;
        $pv_series = $request->pv_series;
        $ld_inp_series = $request->ld_inp_series;
        $no_ld_sharing = $request->no_ld_sharing;
        $direct_pv_run = $request->direct_pv_run;
        $wapda_check = $request->wapda_check;
        $ovl_test_bat = $request->ovl_test_bat;
        $check_on_grid = $request->check_on_grid;
        $off_grid = $request->off_grid;
        $button_test = $request->button_test;
        $ck_amp_d = $request->ck_amp_d;
        $short_circuit = $request->short_circuit;
        $inp_series = $request->inp_series;
        $agm_chg_amp = $request->agm_chg_amp;
        $fld_chg_amp = $request->fld_chg_amp;
        $dual_out_test = $request->dual_out_test;
        $ovl_test_power = $request->ovl_test_power;
        $inductive_load_test = $request->inductive_load_test;
        $total_time = $request->total_time;
        $test_by = Auth::user()->id;

        $check_serial_number = MachineTesting::where('barcode', $serial_number)->first();
        if(!empty($check_serial_number))
        {
            return redirect()->back()->with('danger', 'This Serial Number has been already tested');
        }

        $data = [
            'barcode' => $serial_number,
            'supply_amp' => $supply_amp,
            'no_load_voltage' => $no_load_voltage,
            'wifi_light' => $wifi_light,
            'pv_series' => $pv_series,
            'ld_inp_series' => $ld_inp_series,
            'no_ld_sharing' => $no_ld_sharing,
            'direct_pv_run' => $direct_pv_run,
            'wapda_check' => $wapda_check,
            'ovl_test_bat' => $ovl_test_bat,
            'check_on_grid' => $check_on_grid,
            'off_grid' => $off_grid,
            'button_test' => $button_test,
            'ck_amp_d' => $ck_amp_d,
            'short_circuit' => $short_circuit,
            'inp_series' => $inp_series,
            'agm_chg_amp' => $agm_chg_amp,
            'fld_chg_amp' => $fld_chg_amp,
            'dual_out_test' => $dual_out_test,
            'ovl_test_power' => $ovl_test_power,
            'inductive_load_test' => $inductive_load_test,
            'total_time' => $total_time,
            'test_by' => $test_by,
        ];

        MachineTesting::create($data);

        return redirect()->route('view-testing-list')->with('message', 'Data Saved Successfully!');
    }

    public function viewTestingList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "View Machine Testing List"]
        ];
        $user_id = Auth::user()->id;
        if(Auth::user()->role_id == 2)
        {
            $machines = MachineTesting::where('test_by', $user_id)->get();
        }
        else
        {
            $machines = MachineTesting::all();
        }

        return view('user_portal.MachineTesting.view-testing-list',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'machines'=>$machines]);
    }

    public function assignUnit()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Assign Unit to Tested Board"]
        ];
        return view('user_portal.MachineTesting.assign-unit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveAssignUnit(Request $request)
    {
        $serial_number = $request->serial_number;
        $unit_barcode = $request->unit_barcode;

        $check_serial_number = MachineTesting::where('barcode', $serial_number)->first();
        if(!empty($check_serial_number))
        {
            //Tested Exist
            if(!empty($check_serial_number->machine_barcode)) //unit barcode exists
            {
                $message = 'Mother Board Serial Number '.$serial_number.' has already been assign to '.$check_serial_number->machine_barcode.'!';
                return redirect()->back()->with('danger', $message);
            }
            else
            {
                $check_unit_number = MachineTesting::where('machine_barcode', $unit_barcode)->first();
                if(!empty($check_unit_number))
                {
                    $message = 'Unit Barcode Serial Number '.$unit_barcode.' has already been assign to this Mother Board # '.$check_unit_number->barcode.'!';
                    return redirect()->back()->with('danger', $message);
                }
                else
                {
                    $check_serial_number->update(['machine_barcode'=> $unit_barcode]);
                    return redirect()->route('view-testing-list')->with('message', 'Mother Board Assigned to Unit Successfully!');
                }
            }
        }
        else
        {
            //Not Exist, Show Error
            $message = 'Mother Board Serial Number '.$serial_number.' has not been Tested yet!';
            return redirect()->back()->with('danger', $message);
        }
    }
}
