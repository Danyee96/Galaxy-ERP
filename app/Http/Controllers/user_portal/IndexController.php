<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemList;
use App\Models\ItemHistory;
use App\Models\ItemHistoryAccessory;
use App\Models\Company;
use App\Models\ErrorInfo;
use App\Models\Power;
use App\Models\Accessories;
use App\Models\User;
use App\Models\UPSModel;
use DB;
use Auth;
use PDF;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function index()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Welcome to Dashboard"]
        ];

        $initial_first_day_of_month = new Carbon('first day of this month');
        $initial_last_day_of_month = new Carbon('last day of this month');
        $let_first_day = $initial_first_day_of_month->format('Y-m-d');
        $let_last_day = $initial_last_day_of_month->format('Y-m-d');
        $first_day_of_month = $let_first_day.' 00:00:00';
        $last_day_of_month = $let_last_day.' 23:59:59';

        // Fetch dashboard totals and chart data with grouped aggregate queries.
        $overall_counts = ItemHistory::selectRaw('COUNT(*) as total, SUM(status = 0) as pending, SUM(status = 1) as completed')->first();
        $monthly_counts = ItemHistory::whereBetween('created_at', [$first_day_of_month, $last_day_of_month])
            ->selectRaw('COUNT(*) as total, SUM(status = 0) as pending, SUM(status = 1) as completed')
            ->first();

        $total_complains = (int) $overall_counts->total;
        $total_pending_complains = (int) $overall_counts->pending;
        $total_completed_complains = (int) $overall_counts->completed;
        $total_monthly_complains = (int) $monthly_counts->total;
        $total_monthly_pending_complains = (int) $monthly_counts->pending;
        $total_monthly_completed_complains = (int) $monthly_counts->completed;

        $companies = Company::select('id', 'name')->get();
        $company_counts = ItemHistory::select('company_id')->selectRaw('COUNT(*) as count')->groupBy('company_id')->pluck('count', 'company_id');
        $company_name = $companies->pluck('name')->all();
        $company_data = $companies->pluck('id')->map(function ($id) use ($company_counts) {
            return (int) $company_counts->get($id, 0);
        })->all();

        $powers = Power::select('id', 'name')->get();
        $power_counts = ItemHistory::select('power_id')->selectRaw('COUNT(*) as count')->groupBy('power_id')->pluck('count', 'power_id');
        $power_name = $powers->pluck('name')->all();
        $power_data = $powers->pluck('id')->map(function ($id) use ($power_counts) {
            return (int) $power_counts->get($id, 0);
        })->all();

        $errors = ErrorInfo::select('id', 'name')->get();
        $error_counts = ItemHistory::select('error_id')->selectRaw('COUNT(*) as count')->groupBy('error_id')->pluck('count', 'error_id');
        $error_name = $errors->pluck('name')->all();
        $error_data = $errors->pluck('id')->map(function ($id) use ($error_counts) {
            return (int) $error_counts->get($id, 0);
        })->all();

        $accessories = Accessories::select('id', 'name')->get();
        $accessory_counts = ItemHistoryAccessory::select('accessory_id')->selectRaw('COUNT(*) as count')->groupBy('accessory_id')->pluck('count', 'accessory_id');
        $accessory_name = $accessories->pluck('name')->all();
        $accessory_data = $accessories->pluck('id')->map(function ($id) use ($accessory_counts) {
            return (int) $accessory_counts->get($id, 0);
        })->all();

        //complain in out comparison
        $start_date = strtotime($first_day_of_month);
        $start_date = date("d", $start_date);
        
        $end_date = strtotime($last_day_of_month);
        $end_date = date("d", $end_date);

        $in_complain = ItemHistory::whereBetween('entry_date', [$first_day_of_month, $last_day_of_month])
            ->selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $monthly_in_count_arr  = [];
        $monthly_days_arr =[];
        for ($i=(int)$start_date; $i <=$end_date; $i++)
        {
            $monthly_days_arr[$i]=$i.' '.date('M');
            $monthly_in_count_arr[$i] = (int) $in_complain->get($i, 0);
        }

        $out_complain = ItemHistory::whereBetween('exit_date', [$first_day_of_month, $last_day_of_month])
            ->selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $monthly_out_count_arr  = [];
        $monthly_out_days_arr =[];
        for ($i=(int)$start_date; $i <=$end_date; $i++)
        {
            $monthly_out_days_arr[$i]=$i.' '.date('M');
            $monthly_out_count_arr[$i] = (int) $out_complain->get($i, 0);
        }

        return view('user_portal.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'total_complains'=>$total_complains, 'total_pending_complains'=>$total_pending_complains, 'total_completed_complains'=>$total_completed_complains, 'total_monthly_complains'=>$total_monthly_complains, 'total_monthly_pending_complains'=>$total_monthly_pending_complains, 'total_monthly_completed_complains'=>$total_monthly_completed_complains, 'company_data'=>$company_data, 'company_name'=>$company_name, 'power_data'=>$power_data, 'power_name'=>$power_name,'error_data'=>$error_data,'error_name'=>$error_name,'accessory_data'=>$accessory_data,'accessory_name'=>$accessory_name,'monthly_in_count_arr'=>$monthly_in_count_arr,'monthly_days_arr'=>$monthly_days_arr,'monthly_out_count_arr'=>$monthly_out_count_arr,'monthly_out_days_arr'=>$monthly_out_days_arr]);
    }

    public function count_order($arr)
    {
        return count($arr);
    }

    public function logout() 
    {
        Auth::logout();
        return redirect()->route('getLogin');
    }

    public function searchMachine()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Search Machine"]
        ];
        return view('user_portal.Item.check-item',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function searchMachineResult(Request $request)
    {
        $serial_number = $request->name;
        $find_item = ItemList::where('serial_number',$serial_number)->first();
        if(!empty($find_item))
        {
            return redirect()->route('fetchMachineDetails',$find_item->id);
        }
        else
        {
            return redirect()->route('addNewMachineInfo',$serial_number);
        }
    }

    public function addNewMachineInfo($serial_number)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add Machine Info"]
        ];
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $companies = Company::where('status', 1)->get();
        $accessories = Accessories::all();
        $errors = ErrorInfo::all();
        $powers = Power::all();
        $models = UPSModel::all();
        $users = User::whereIn('id',[1,2])->get();
        return view('user_portal.Item.create-machine',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'companies'=>$companies,'accessories'=>$accessories,'errors'=>$errors,'powers'=>$powers,'today_date'=>$today_date,'serial_number'=>$serial_number,'users'=>$users,'models'=>$models]);
    }

    public function saveNewMachineInfo(Request $request)
    {
        $serial_number = $request->serial_number;
        $customer_name = $request->customer_name;
        $company_id = 3;
        $entry_date = $request->entry_date;
        $phone = $request->phone;
        $power_id = $request->power_id;
        $error_id = $request->error_id;
        $error_description = $request->error_description;
        $maker_name = $request->maker_name;
        $model = $request->model;
        $production_date = $request->production_date;
        $warranty_year = $request->warranty_year;
        $user = $request->user;

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $toDate = Carbon::parse($today_date);
        $fromDate = Carbon::parse($production_date);
  
        $days = 365 * $request->warranty_year;
  
        $warranty_date = $toDate->diffInDays($fromDate);
        if($warranty_date > $days)
        {
            $warranty_check = 'out';
        }
        else
        {
            $warranty_check = 'in';
        }

        $check_item = ItemList::where('serial_number', $serial_number)->first();
        if(empty($check_item))
        {
            $item_data = [
                'serial_number' => $serial_number,
                'power_id' => $power_id,
                'company_id' => $company_id,
                'maker_name' => $maker_name,
                'model' => $model,
                'production_date' => $production_date,
                'warranty_years' => $warranty_year
            ];

            $create_item = ItemList::create($item_data);

            if($create_item)
            {
                $history_date = [
                    'item_id' => $create_item->id,
                    'company_id' => $company_id,
                    'name' => $customer_name,
                    'phone' => $phone,
                    'entry_date' => $entry_date,
                    'error_id' => $error_id,
                    'error_description' => $error_description,
                    'maker_name' => $maker_name,
                    'model' => $model,
                    'power_id' => $power_id,
                    'status' => 0,
                    'created_by' => Auth::user()->id,
                    'warranty_status' => $warranty_check,
                    'entry_by' => Auth::user()->id,
                ];

                $create_history = ItemHistory::create($history_date);

                if ($request->has('accessory')) {
                    $accessories = $request->accessory;
                    foreach ($accessories as $keyTwo => $accessory) {
                        $accessory_data = [
                            'item_history_id' => $create_history->id,
                            'accessory_id' => $accessory,
                            'status' => 0,
                        ];
                        ItemHistoryAccessory::create($accessory_data);
                    }
                }
            }

            // $machine = ItemHistory::where('id', $create_history->id)->first();
            // $pdf = PDF::loadView('user_portal.Item.print-slip', compact('machine'))->setOptions(['defaultFont' => 'sans-serif']);
            // $pdf_name = "Machine_Data.pdf";
            // return $pdf->download($pdf_name);

            return redirect()->route('print-slip', $create_history->id)->with('message','New Machine Data Saved Successfully, search to see Machine Details');
        }
        else
        {
            return redirect()->route('searchMachine')->with('message','Search to see Machine Details');
        }
    }

    public function fetchMachineDetails($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/search-machine", "name" => "Search Machine"],["name" => "Machine History"]
        ];
        $find_item = ItemList::find($id);
        $today_date = Carbon::now(+5)->format('Y-m-d');
        // dd($find_item);
        $open_machine_history = ItemHistory::where('item_id', $id)->where('status',0)->orderBy('id','DESC')->get();
        $close_machine_history = ItemHistory::where('item_id', $id)->where('status',1)->orderBy('id','DESC')->get();
        $close_machine_history_cost = ItemHistory::where('item_id', $id)->where('status',1)->orderBy('id','DESC')->sum('cost');
        // dd($close_machine_history_cost);
        return view('user_portal.Item.machine-history',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'find_item'=>$find_item,'open_machine_history'=>$open_machine_history,'close_machine_history'=>$close_machine_history,'close_machine_history_cost'=>$close_machine_history_cost,'today_date'=>$today_date]);
    }

    public function addMachineInfo($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/fetch-machine-details/{$id}", "name" => "Back to History"],["name" => "Add Machine Info"]
        ];
        $serial_number = ItemList::where('id',$id)->first();
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $companies = Company::where('status', 1)->get();
        $accessories = Accessories::all();
        $errors = ErrorInfo::all();
        $powers = Power::all();
        $models = UPSModel::all();
        $users = User::whereIn('id',[1,2])->get();
        return view('user_portal.Item.add-machine-info',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'companies'=>$companies,'accessories'=>$accessories,'errors'=>$errors,'powers'=>$powers,'today_date'=>$today_date,'serial_number'=>$serial_number,'users'=>$users,'models'=>$models]);
    }

    public function saveMachineInfo(Request $request)
    {
        $serial_number = $request->serial_number;
        $customer_name = $request->customer_name;
        $entry_date = $request->entry_date;
        $phone = $request->phone;
        $error_id = $request->error_id;
        $error_description = $request->error_description;
        $user = $request->user;

        $create_item = ItemList::where('serial_number',$serial_number)->first();

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $toDate = Carbon::parse($today_date);
        $fromDate = Carbon::parse($create_item->production_date);
  
        $days = 365 * $create_item->warranty_year;
  
        $warranty_date = $toDate->diffInDays($fromDate);
        if($warranty_date > $days)
        {
            $warranty_check = 'out';
        }
        else
        {
            $warranty_check = 'in';
        }

        if($create_item)
        {
            $history_date = [
                'item_id' => $create_item->id,
                'company_id' => $create_item->company_id,
                'name' => $customer_name,
                'phone' => $phone,
                'entry_date' => $entry_date,
                'error_id' => $error_id,
                'error_description' => $error_description,
                'maker_name' => $create_item->maker_name,
                'model' => $create_item->model,
                'power_id' => $create_item->power_id,
                'status' => 0,
                'created_by' => Auth::user()->id,
                'warranty_status' => $warranty_check,
                'entry_by' => Auth::user()->id,
            ];

            $create_history = ItemHistory::create($history_date);

            if ($request->has('accessory')) {
                $accessories = $request->accessory;

                foreach ($accessories as $keyTwo => $accessory) {
                    $accessory_data = [
                        'item_history_id' => $create_history->id,
                        'accessory_id' => $accessory,
                        'status' => 0,
                    ];

                    ItemHistoryAccessory::create($accessory_data);
                }
            }
        }

        // $machine = ItemHistory::where('id', $create_history->id)->first();
        // $pdf = PDF::loadView('user_portal.Item.print-slip', compact('machine'))->setOptions(['defaultFont' => 'sans-serif']);
        // $pdf_name = "Machine_Data.pdf";
        // return $pdf->download($pdf_name);

        return redirect()->route('print-slip',$create_history->id)->with('message','New Machine Record Added Successfully');
    }

    public function markAsPacked($id)
    {
        $item_history = ItemHistory::where('id',$id)->first();
        $item_history->update(['packing_status'=>1]);

        return redirect()->back()->with('message','Machine Marked as Packed Successfully');
    }

    public function exitMachine($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/fetch-machine-details/{$id}", "name" => "Back to History"],["name" => "Exit Machine"]
        ];
        $item_history = ItemHistory::where('id',$id)->first();
        $item_history_accessories = ItemHistoryAccessory::where('item_history_id', $id)->get();
        $today_date = Carbon::now(+5)->format('Y-m-d');
        return view('user_portal.Item.exit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'today_date'=>$today_date,'item_history'=>$item_history,'item_history_accessories'=>$item_history_accessories]);
    }

    public function saveExitMachine(Request $request, $id)
    {
        $find_history = ItemHistory::where('id', $id)->first();
        $issue = $request->issue;
        $solution = $request->solution;
        $receiver_name = $request->receiver_name;
        $cost = $request->cost;
        $today_date = Carbon::now(+5)->format('Y-m-d');

        $data = [
            'resolution' => $solution,
            'error_description' => $issue,
            'receiver_name' => $receiver_name,
            'cost' => $cost,
            'exit_by' => Auth::user()->id,
            'exit_date' => $today_date,
            'status' => 1,
        ];

        $find_history->update($data);

        if ($request->has('accessory')) {
            $accessories = $request->accessory;

            foreach ($accessories as $keyTwo => $accessory) {
                $find_accessory = ItemHistoryAccessory::find($accessory);
                $find_accessory->update(['status'=>1]);
            }
        }

        return redirect()->route('fetchMachineDetails',$find_history->item_id)->with('message','Machine Record Updated Successfully');
    }

    public function reporting(Request $request)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Reporting"]
        ];
        $companies = Company::all();
        $powers = Power::all();
        $errors = ErrorInfo::all();
        $today_date = Carbon::now(+5)->format('Y-m-d');

        // Build query with eager loading to prevent N+1 queries
        $query = ItemHistory::with(['Item', 'Company', 'Power', 'Error', 'EntryBy', 'ExitBy', 'machineAccessories.Accessory'])->orderBy('created_at','DESC');

        // Apply filters
        if ($request->from && $request->to) {
            $query->whereDate('entry_date', '>=', $request->from)
                  ->whereDate('entry_date', '<=', $request->to);
        }
        
        if ($request->company && $request->company != 'any') {
            $query->where('company_id', $request->company);
        }
        
        if ($request->power && $request->power != 'any') {
            $query->where('power_id', $request->power);
        }
        
        if ($request->error && $request->error != 'any') {
            $query->where('error_id', $request->error);
        }

        // Limit results to prevent timeout (max 5000 records)
        $machine_histories = $query->limit(5000)->get();
        
        return view('user_portal.reporting',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'today_date'=>$today_date,'companies'=>$companies,'powers'=>$powers,'errors'=>$errors,'machine_histories'=>$machine_histories]);
    }

    public function downloadMachineData()
    {
        // Limit to recent records and use eager loading to prevent memory issues
        // Only load essential columns and limit to last 1000 records
        $machines = ItemList::with(['Company', 'Power'])
            ->select('id', 'serial_number', 'company_id', 'power_id', 'maker_name', 'model', 'production_date', 'warranty_years')
            ->orderBy('created_at', 'DESC')
            ->limit(1000)
            ->get();
        
        $machine_cost = ItemHistory::where('status',1)->sum('cost');
        // return view('user_portal.download-reporting', compact('machines','machine_cost'));
        $pdf = PDF::loadView('user_portal.download-reporting', compact('machines','machine_cost'));
        $pdf_name = "All_Machine_Data.pdf";
        return $pdf->download($pdf_name);
    }

    public function machineList(Request $request)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "In Factory Machine List"]
        ];
        $companies = Company::all();
        $today_date = Carbon::now(+5)->format('Y-m-d');

        // Build query with eager loading
        $query = ItemHistory::with(['Item', 'Company', 'Power', 'Error'])->orderBy('created_at','DESC');

        // Apply filters
        if ($request->from && $request->to) {
            $query->whereDate('entry_date', '>=', $request->from)
                  ->whereDate('entry_date', '<=', $request->to);
        }
        
        if ($request->company && $request->company != 'any') {
            $query->where('company_id', $request->company);
        }

        // Limit results to prevent timeout (max 5000 records)
        $machine_histories = $query->limit(5000)->get();

        return view('user_portal.Item.machine-list',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'today_date'=>$today_date,'companies'=>$companies,'machine_histories'=>$machine_histories]);
    }

    public function printSlip(Request $request)
    {
        $lists = $request->lists;
        $machines = ItemHistory::whereIn('id',$lists)->get();
        return view('user_portal.Item.machine-slip',compact('machines'));
    }

    public function singlePrintSlip($id)
    {
        $machine = ItemHistory::where('id', $id)->first();
        return view('user_portal.Item.single-machine-slip',compact('machine'));
    }

    public function singleBarcodePrintSlip($id)
    {
        $machine = ItemHistory::where('id', $id)->first();
        return view('user_portal.Item.barcode-machine-slip',compact('machine'));
    }

    public function overallPending()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Overall Pending Complains"]
        ];

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $machine_histories = ItemHistory::where('status',0)->orderBy('created_at','DESC')->get();
        return view('user_portal.Report.pending',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'machine_histories'=>$machine_histories,'today_date'=>$today_date]);
    }

    public function monthlyPending()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Monthly Pending Complains"]
        ];

        $initial_first_day_of_month = new Carbon('first day of this month');
        $initial_last_day_of_month = new Carbon('last day of this month');
        $let_first_day = $initial_first_day_of_month->format('Y-m-d');
        $let_last_day = $initial_last_day_of_month->format('Y-m-d');
        $first_day_of_month = $let_first_day.' 00:00:00';
        $last_day_of_month = $let_last_day.' 23:59:59';

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $machine_histories = ItemHistory::where('created_at', '>=', $first_day_of_month)->where('created_at', '<=', $last_day_of_month)->where('status',0)->orderBy('created_at','DESC')->get();
        return view('user_portal.Report.pending',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'machine_histories'=>$machine_histories,'today_date'=>$today_date]);
    }

    public function overallComplete()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Overall Resolve Complains"]
        ];

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $machine_histories = ItemHistory::where('status',1)->orderBy('created_at','DESC')->get();
        return view('user_portal.Report.complete',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'machine_histories'=>$machine_histories,'today_date'=>$today_date]);
    }

    public function monthlyComplete()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Monthly Resolve Complains"]
        ];

        $initial_first_day_of_month = new Carbon('first day of this month');
        $initial_last_day_of_month = new Carbon('last day of this month');
        $let_first_day = $initial_first_day_of_month->format('Y-m-d');
        $let_last_day = $initial_last_day_of_month->format('Y-m-d');
        $first_day_of_month = $let_first_day.' 00:00:00';
        $last_day_of_month = $let_last_day.' 23:59:59';

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $machine_histories = ItemHistory::where('created_at', '>=', $first_day_of_month)->where('created_at', '<=', $last_day_of_month)->where('status',1)->orderBy('created_at','DESC')->get();
        return view('user_portal.Report.complete',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'machine_histories'=>$machine_histories,'today_date'=>$today_date]);
    }

    public function customMachineData()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Custom Machine Report"]
        ];
        $companies = Company::all();
        $powers = Power::all();
        $errors = ErrorInfo::all();
        $today_date = Carbon::now(+5)->format('Y-m-d');

        return view('user_portal.Report.custom-machine-report',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'today_date'=>$today_date,'companies'=>$companies,'powers'=>$powers,'errors'=>$errors]);
    }

    public function customMachineDataPDF(Request $request)
    {
        if ($request->from && $request->to && $request->company == 'any' && $request->power == 'any' && $request->error == 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company == 'any' && $request->power == 'any' && $request->error <> 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.error_id', '=', $request->error)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company == 'any' && $request->power <> 'any' && $request->error == 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.power_id', '=', $request->power)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company <> 'any' && $request->power == 'any' && $request->error == 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.company', '=', $request->company)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company == 'any' && $request->power <> 'any' && $request->error <> 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.power_id', '=', $request->power)
                ->where('item_histories.error_id', '=', $request->error)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company <> 'any' && $request->power <> 'any' && $request->error == 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.power_id', '=', $request->power)
                ->where('item_histories.company_id', '=', $request->company)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company <> 'any' && $request->power == 'any' && $request->error <> 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.company_id', '=', $request->company)
                ->where('item_histories.error_id', '=', $request->error)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
        if ($request->from && $request->to && $request->company <> 'any' && $request->power <> 'any' && $request->error <> 'any')
        {
            $machine_details = DB::table('item_histories')
                ->join('item_lists', 'item_lists.id', 'item_histories.item_id')
                ->join('powers', 'powers.id', 'item_lists.power_id')
                ->join('companies', 'companies.id', 'item_lists.company_id')
                ->whereDate('item_histories.entry_date', '>=', $request->from)
                ->whereDate('item_histories.entry_date', '<=', $request->to)
                ->where('item_histories.status', '=', 1)
                ->where('item_histories.power_id', '=', $request->power)
                ->where('item_histories.error_id', '=', $request->error)
                ->where('item_histories.company_id', '=', $request->company)
                ->select(
                    DB::raw('item_lists.serial_number as serial_number'),
                    DB::raw('item_lists.model as model'),
                    DB::raw('powers.name as power'),
                    DB::raw('companies.name as company'),
                    DB::raw('sum(item_histories.cost) as total'),
                    DB::raw('count(item_histories.id) as count_total'),
                )
                ->groupBy(DB::raw('serial_number'))->groupBy(DB::raw('power'))->groupBy(DB::raw('model'))->groupBy(DB::raw('company'))
                ->get();

            $machine_cost = ItemHistory::with('Item')->whereDate('entry_date', '>=', $request->from)->whereDate('entry_date', '<=', $request->to)->where('status',1)->sum('cost');
            // return view('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf = PDF::loadView('user_portal.Report.custom-machine-report-download', compact('machine_details','machine_cost'));
            $pdf_name = "Custom_Machine_Data.pdf";
            return $pdf->download($pdf_name);
        }
    }

    public function createNewMachineInfo()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Create Machine Info"]
        ];
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $companies = Company::all();
        $powers = Power::all();
        return view('user_portal.Machine.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'companies'=>$companies,'powers'=>$powers,'today_date'=>$today_date]);
    }

    public function saveNewCreateMachineInfo(Request $request)
    {
        $serial_number = $request->serial_number;
        $company_id = $request->company_id;
        $power_id = $request->power_id;
        $maker_name = $request->maker_name;
        $model = $request->model;
        $production_date = $request->production_date;
        $warranty_year = $request->warranty_year;

        $item_data = [
            'serial_number' => $serial_number,
            'power_id' => $power_id,
            'company_id' => $company_id,
            'maker_name' => $maker_name,
            'model' => $model,
            'production_date' => $production_date,
            'warranty_years' => $warranty_year,
        ];

        $create_item = ItemList::create($item_data);

        return redirect()->back()->with('message','Machine Data Saved Successfully');
    }

    public function customMachineList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Machine List"]
        ];

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $machines = ItemList::orderBy('created_at','DESC')->get();
        return view('user_portal.Machine.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'machines'=>$machines,'today_date'=>$today_date]);
    }

    public function warrantyCheck(Request $request)
    {
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $toDate = Carbon::parse($today_date);
        $fromDate = Carbon::parse($request->date);

        $days = 365 * $request->year;
  
        $warranty_date = $toDate->diffInDays($fromDate);
        if($warranty_date > $days)
        {
            $warranty_check = 'out';
        }
        else
        {
            $warranty_check = 'in';
        }

        return response()->json(['response' => $warranty_check]);
    }

    public function editMachine($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/fetch-machine-details/{$id}", "name" => "Back to History"],["name" => "Edit Machine"]
        ];
        $item_history = ItemHistory::where('id',$id)->first();

        $serial_number = ItemList::where('id',$item_history->item_id)->first();
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $companies = Company::where('status',1)->get();        
        $errors = ErrorInfo::all();
        $powers = Power::all();
        $models = UPSModel::all();
        $users = User::whereIn('id',[1,2])->get();
        return view('user_portal.Item.edit-machine-info',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'companies'=>$companies,'errors'=>$errors,'powers'=>$powers,'today_date'=>$today_date,'serial_number'=>$serial_number,'users'=>$users, 'item_history'=>$item_history,'models'=>$models]);
    }

    public function saveEditMachine(Request $request, $id)
    {
        $customer_name = $request->customer_name;
        $model = $request->model;
        $phone = $request->phone;

        $item_history = ItemHistory::where('id',$id)->first();
        $serial_number = ItemList::where('id',$item_history->item_id)->first();

        $item_data = [
            'model' => $model,
        ];

        $serial_number->update($item_data);

        $item_history_data = [
            'name' => $customer_name,
            'model' => $model,
            'phone' => $phone,
        ];

        $item_history->update($item_history_data);

        return redirect()->route('fetchMachineDetails',$serial_number->id)->with('message','Machine Record Updated Successfully');
    }

    public function printThreeSlip($id)
    {
        $machine = ItemHistory::where('id', $id)->first();
        return view('user_portal.Item.print-slip',compact('machine'));
    }
}
