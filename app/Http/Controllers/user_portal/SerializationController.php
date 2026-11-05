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
    public function serializationList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Serialization Info List"]
        ];
        $machines = Serialization::all();
        return view('user_portal.Serialization.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'machines' => $machines]);
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
