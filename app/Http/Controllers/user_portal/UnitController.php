<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use Auth;

class UnitController extends Controller
{
    public function index()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Unit List"]
        ];
        $units = Unit::where('status', 1)->get();
        return view('user_portal.Inventory.unit.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'units' => $units]);
    }

    public function create()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add Unit"]
        ];
        return view('user_portal.Inventory.unit.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveUnit(Request $request)
    {
        $name = $request->unit_name;
        $code = $request->unit_code;

        $data = [
            'name' => $name,
            'unit' => $code,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
            'status' => 1
        ];

        Unit::create($data);

        return redirect()->route('unit-list')->with('message', 'Unit Saved Successfully!');
    }
}
