<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Serialization;
use App\Models\UPSModel;
use Auth;
use Carbon\Carbon;

class SerializationController extends Controller
{
    public function serializationList(Request $request)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Serialization Info List"]
        ];
        $columns = ['serial_number'=>'Serial Number','date'=>'Date','model'=>'Model','dealer_name'=>'Dealer Name','dealer_city'=>'Dealer City','customer_name'=>'Customer Name','customer_city'=>'Customer City','delivery_boy'=>'Delivery Boy'];
        $filters = $request->validate([
            'search'=>'nullable|string|max:200',
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>['nullable','date_format:Y-m-d',...($request->filled('from') ? ['after_or_equal:from'] : [])],
            'serial_number'=>'nullable|string|max:255',
            'model'=>'nullable|string|max:255',
            'dealer_name'=>'nullable|string|max:255',
            'dealer_city'=>'nullable|string|max:255',
            'customer_name'=>'nullable|string|max:255',
            'customer_city'=>'nullable|string|max:255',
            'delivery_boy'=>'nullable|string|max:255',
            'field'=>'nullable|in:'.implode(',',array_keys($columns)),
            'value'=>'nullable|string|max:255',
            'sort'=>'nullable|in:'.implode(',',array_keys($columns)),
            'direction'=>'nullable|in:asc,desc',
            'print'=>'nullable|boolean',
        ]);
        $query = Serialization::query();
        if ($term = trim($filters['search'] ?? '')) {
            $query->where(function($q) use ($columns,$term) {
                foreach(array_keys($columns) as $column) $q->orWhere($column,'like','%'.$term.'%');
            });
        }
        // Preserve existing filter links while showing individual fields in the form.
        if (!empty($filters['field']) && isset($filters['value']) && $filters['value'] !== '') {
            $filters[$filters['field']] = $filters[$filters['field']] ?? $filters['value'];
        }
        foreach ($columns as $column=>$label) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column,$column === 'serial_number' ? 'like' : '=', $column === 'serial_number' ? '%'.$filters[$column].'%' : $filters[$column]);
            }
        }
        if (!empty($filters['from'])) $query->where('date','>=',$filters['from']);
        if (!empty($filters['to'])) $query->where('date','<=',$filters['to']);
        $query->orderBy($filters['sort'] ?? 'id',$filters['direction'] ?? 'asc');
        if (!empty($filters['sort'])) $query->orderBy('id');
        if ($request->boolean('print')) {
            return view('user_portal.Serialization.print',['machines'=>$query->cursor(),'filters'=>$filters,'columns'=>$columns]);
        }
        $values = [];
        foreach (array_diff(array_keys($columns),['serial_number','date']) as $column) {
            $values[$column] = Serialization::query()->whereNotNull($column)->where($column,'<>','')->distinct()->orderBy($column)->pluck($column);
        }
        $machines = $query->paginate(50)->withQueryString();
        return view('user_portal.Serialization.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'machines' => $machines,'filters'=>$filters,'columns'=>$columns,'values'=>$values]);
    }

    public function createSerialization()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add New Serialization"]
        ];

        $today_date = Carbon::now(+5)->format('Y-m-d');
        $models = UPSModel::all();
        return view('user_portal.Serialization.create-new',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'today_date'=>$today_date, 'models' => $models]);
    }

    public function saveSerialization(Request $request)
    {
        $check_item = Serialization::where('serial_number', $request->serial_number)->first();
        if(!empty($check_item))
        {
            $message = 'Duplicate Serial Number Entered '.$request->serial_number;
            return redirect()->back()->with('error', $message);
        }
        else
        {
            $serial_number = $request->serial_number;
            $model = $request->model;
            $production_date = $request->production_date;
            $dealer_name = $request->dealer_name;
            $dealer_city = $request->dealer_city;
            $delivery_boy = $request->delivery_boy;
            $customer_name = $request->customer_name;
            $customer_city = $request->customer_city;
            $created_by = Auth::user()->id;

            $data = [
                'serial_number' => $serial_number,
                'date' => $production_date,
                'model' => $model,
                'dealer_name' => $dealer_name,
                'dealer_city' => $dealer_city,
                'customer_name' => $customer_name,
                'customer_city' => $customer_city,
                'delivery_boy' => $delivery_boy,
                'created_by' => $created_by,
            ];

            Serialization::create($data);

            return redirect()->route('serialization-list')->with('message','Serialization Info Saved Successfully');
        }
    }

    public function addSerialization(Request $request)
    {
        $authUser = Auth::user()->id;

        $serial_number = $request->serial_number;
        $model_id = $request->model_id;
        $production_date = $request->production_date;
        $dealer_name = $request->dealer_name;
        $dealer_city = $request->dealer_city;
        $delivery_boy = $request->delivery_boy;
        $customer_name = $request->customer_name;
        $customer_city = $request->customer_city;

        if($serial_number == null)
        {
            $message_show = 'Please Scan Serial Number';

            $status = 'Error';
            $message = $message_show;
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        if($model_id == null)
        {
            $message_show = 'Please Select Model';

            $status = 'Error';
            $message = $message_show;
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        if($dealer_name == null)
        {
            $message_show = 'Please Enter Dealer Name';

            $status = 'Error';
            $message = $message_show;
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        if($delivery_boy == null)
        {
            $message_show = 'Please Enter Deliver Boy Name';

            $status = 'Error';
            $message = $message_show;
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        $check_serial_number = Serialization::where('serial_number', $request->serial_number)->first();

        if(!empty($check_serial_number))
        {
            $message_show = 'Duplicate Serial Number Entered '.$request->serial_number;

            $status = 'Already';
            $message = $message_show;
            $data = 0;
        }
        else
        {
            $data = [
                'serial_number' => $serial_number,
                'date' => $production_date,
                'model' => $model_id,
                'dealer_name' => $dealer_name,
                'dealer_city' => $dealer_city,
                'customer_name' => $customer_name,
                'customer_city' => $customer_city,
                'delivery_boy' => $delivery_boy,
                'created_by' => $authUser,
            ];

            $model_info = Serialization::create($data);

            $latest_model = Serialization::where('id', $model_info->id)->first();

            $status = 'Success';
            $message = 'Serialization Info Saved Successfully';
            $data = $latest_model;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'finished_model' => $data,
        ];

        return response()->json($response);
    }
}
