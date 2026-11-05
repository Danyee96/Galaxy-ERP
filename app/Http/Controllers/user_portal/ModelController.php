<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\UPSModel;
use App\Models\ModelRecipe;
use App\Models\FinishedModels;
use App\Models\FinishedModelMaterial;
use Carbon\Carbon;
use Auth;

class ModelController extends Controller
{
    public function upsModel()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "UPS Models List"]
        ];
        $models = UPSModel::all();
        return view('user_portal.Inventory.model.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'models' => $models]);
    }

    public function createUpsModel()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add UPS Model"]
        ];
        return view('user_portal.Inventory.model.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveUpsModel(Request $request)
    {
        $name = $request->material_name;
        $current_stock = $request->current_stock;
        $current_semi_stock = $request->current_semi_stock;

        $data = [
            'name' => $name,
            'qty' => $current_stock,
            'temp_qty' => $current_stock,
            'semi_qty' => $current_semi_stock,
            'semi_temp_qty' => $current_semi_stock,
            'status' => 1,
        ];

        UPSModel::create($data);

        return redirect()->route('ups-model-list')->with('message', 'UPS Model Added Successfully!');
    }

    public function editUpsModel($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/ups-model-list", "name" => "UPS Model List"],["name" => "Edit"]
        ];
        $model = UPSModel::where('id', $id)->first();
        return view('user_portal.Inventory.model.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'model' => $model]);
    }

    public function updateUpsModel(Request $request)
    {
        $name = $request->model_name;
        $model_id = $request->model_id;

        $model = UPSModel::where('id', $model_id)->first();

        $data = [
            'name' => $name,
        ];

        $model->update($data);

        return redirect()->route('ups-model-list')->with('message', 'UPS Model Updated Successfully!');
    }

    //recipe making

    public function addUpsModelRecipe($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/ups-model-list", "name" => "UPS Models List"],["name" => "Add Production Making"]
        ];
        $model = UPSModel::where('id', $id)->first();
        $raw_materials = RawMaterial::all();
        $recipes = ModelRecipe::where('model_id', $id)->get();
        return view('user_portal.Inventory.model.recipe',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'model' => $model, 'raw_materials' => $raw_materials,'recipes'=> $recipes]);
    }

    public function saveUpsModelRecipe(Request $request)
    {
        $menu_item = $request->menu_item_id;

        if($request->has('select_raw_item'))
        {
            $raw_materials = $request->select_raw_item;
            $raw_qty = $request->qty;

            foreach($raw_materials as $key => $raw_material)
            {
                $dateOne = [
                    'model_id' => $menu_item,
                    'raw_material_id' => $raw_materials[$key],
                    'qty' => $raw_qty[$key],
                ];

                ModelRecipe::create($dateOne);
            }
        }

        if($request->has('recipe_id'))
        {
            $recipe_id = $request->recipe_id;
            $raw_qty_old = $request->qty_old;

            foreach($recipe_id as $keyTwo => $recipe_ids)
            {
                $find_recipe = ModelRecipe::where('id', $recipe_ids)->first();
                $dateOne_old = [
                    'qty' => $raw_qty_old[$keyTwo],
                ];

                $find_recipe->update($dateOne_old);
            }
        }

        return redirect()->route('ups-model-list')->with('message', 'Production Recipe Added Successfully!');
    }

    /* finished model */

    public function createFinishedUpsModel()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add Finished UPS Model"]
        ];
        $models = UPSModel::all();
        return view('user_portal.Inventory.model.finished_models.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'models'=>$models]);
    }

    public function addFinishedUpsModel(Request $request)
    {
        $authUser = Auth::user()->id;

        $serial_number_reference = $request->serial_number_reference;
        $model_id = $request->model_id;

        $get_model_recipes = ModelRecipe::where('model_id', $model_id)->get();
        if(count($get_model_recipes) > 0)
        {
            foreach ($get_model_recipes as $get_model_recipe) {

                $inventory = RawMaterial::where('id', $get_model_recipe->raw_material_id)->first();

                if ($inventory->current_stock < $get_model_recipe->qty) {

                    $calculate = $get_model_recipe->qty - $inventory->current_stock;

                    $status = 'NotEnoughInventory';
                    $message = 'Sorry! Not Enough '. $inventory->name . ', Need ' . $calculate . ' More Pcs';
                    $data = 0;

                    $response = [
                        'status' => $status,
                        'message' => $message,
                        'finished_model' => $data,
                    ];

                    return response()->json($response);
                }
                else
                {

                }
            }
        }
        else
        {
            $status = 'NoRecipe';
            $message = 'Sorry! No Production Recipe Added Yet, Add Recipe First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        $check_serial_number = FinishedModels::where('serial_number', $serial_number_reference)->first();

        if(!empty($check_serial_number))
        {
            $status = 'Already';
            $message = 'This Serial Number Has Already been Scanned';
            $data = 0;
        }
        else
        {
            $data = [
                'serial_number' => $serial_number_reference,
                'model_id' => $model_id,
                'created_by' => $authUser,
                'is_complete' => 1,
                'status' => 0,
            ];

            $model = FinishedModels::create($data);

            $get_model_recipes_new = ModelRecipe::where('model_id', $model_id)->get();
            if(count($get_model_recipes_new) > 0)
            {
                foreach ($get_model_recipes_new as $get_model_recipe_new) {
                    $inventory_new = RawMaterial::where('id', $get_model_recipe_new->raw_material_id)->first();

                    $final_value = $inventory_new->current_stock - $get_model_recipe_new->qty;
                    $final_temp_value = $inventory_new->temp_stock - $get_model_recipe_new->qty;

                    $inventory_new->update(['current_stock'=>$final_value, 'temp_stock'=>$final_temp_value]);

                    $material_data = [
                        'finished_model_id' => $model->id,
                        'raw_material_id' => $get_model_recipe_new->raw_material_id,
                        'qty' => $get_model_recipe_new->qty,
                        'remain_qty' => 0,
                        'status' => 1,
                    ];

                    FinishedModelMaterial::create($material_data);
                }
            }

            $latest_model = FinishedModels::with('modelDetail')->where('id', $model->id)->first();

            $status = 'Success';
            $message = 'Finished UPS Added Successfully';
            $data = $latest_model;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'finished_model' => $data,
        ];

        return response()->json($response);
    }

    public function finishedUpsModelList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Finished UPS Models List"]
        ];
        $models = FinishedModels::where('is_complete',1)->orderBy('id','DESC')->get();
        return view('user_portal.Inventory.model.finished_models.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'models'=>$models]);
    }

    public function inCompleteUpsModelList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "In-Complete UPS Models List"]
        ];
        $models = FinishedModels::where('is_complete',0)->orderBy('id','DESC')->get();
        return view('user_portal.Inventory.model.in_complete_models.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'models'=>$models]);
    }

    public function selectModel()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Select In-Complete Model"]
        ];
        $models = UPSModel::all();
        return view('user_portal.Inventory.model.in_complete_models.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'models'=>$models]);
    }

    public function fetchModelRecipe(Request $request)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/select-model", "name" => "Select In-Complete Model"],["name" => "Select Model"]
        ];

        $model_id = $request->model_id;
        $model = UPSModel::where('id', $model_id)->first();
        $model_recipes = ModelRecipe::where('model_id', $model_id)->get();
        return view('user_portal.Inventory.model.in_complete_models.model_recipe',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'model'=>$model,'model_recipes'=>$model_recipes]);
    }

    public function addInCompleteUpsModel(Request $request)
    {
        $authUser = Auth::user()->id;

        $serial_number_reference = $request->serial_number_reference;
        $model_id = $request->model_id;
        $checkedArray = $request->checkedArray;

        $get_model_recipes = ModelRecipe::where('model_id', $model_id)->get();
        if(count($get_model_recipes) > 0)
        {
            foreach ($get_model_recipes as $key => $get_model_recipe) {

                $inventory = RawMaterial::where('id', $get_model_recipe->raw_material_id)->first();

                if ($inventory->current_stock < $checkedArray[$key]) {

                    $calculate = $checkedArray[$key] - $inventory->current_stock;

                    $status = 'NotEnoughInventory';
                    $message = 'Sorry! Not Enough '. $inventory->name . ', Need ' . $calculate . ' More Pcs';
                    $data = 0;

                    $response = [
                        'status' => $status,
                        'message' => $message,
                        'finished_model' => $data,
                    ];

                    return response()->json($response);
                }
                else
                {

                }
            }
        }
        else
        {
            $status = 'NoRecipe';
            $message = 'Sorry! No Production Recipe Added Yet, Add Recipe First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        $check_serial_number = FinishedModels::where('serial_number', $serial_number_reference)->first();

        if(!empty($check_serial_number))
        {
            $status = 'Already';
            $message = 'This Serial Number Has Already been Scanned';
            $data = 0;
        }
        else
        {
            $data = [
                'serial_number' => $serial_number_reference,
                'model_id' => $model_id,
                'created_by' => $authUser,
                'is_complete' => 0,
                'status' => 0,
            ];

            $model = FinishedModels::create($data);

            $get_model_recipes_new = ModelRecipe::where('model_id', $model_id)->get();
            if(count($get_model_recipes_new) > 0)
            {
                foreach ($get_model_recipes_new as $keyOne => $get_model_recipe_new) {
                    $inventory_new = RawMaterial::where('id', $get_model_recipe_new->raw_material_id)->first();

                    $final_value = $inventory_new->current_stock - $checkedArray[$keyOne];
                    $final_temp_value = $inventory_new->temp_stock - $checkedArray[$keyOne];

                    $remain_qty = $get_model_recipe_new->qty - $checkedArray[$keyOne];

                    $inventory_new->update(['current_stock'=>$final_value, 'temp_stock'=>$final_temp_value]);

                    $material_data = [
                        'finished_model_id' => $model->id,
                        'raw_material_id' => $get_model_recipe_new->raw_material_id,
                        'qty' => $checkedArray[$keyOne],
                        'remain_qty' => $remain_qty,
                        'status' => 1,
                    ];

                    FinishedModelMaterial::create($material_data);
                }
            }

            $latest_model = FinishedModels::with('modelDetail')->where('id', $model->id)->first();

            $status = 'Success';
            $message = 'Finished UPS Added Successfully';
            $data = $latest_model;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'finished_model' => $data,
        ];

        return response()->json($response);
    }

    public function markCompleteUpsModel($id)
    {
        $find_details = FinishedModels::where('id', $id)->first();

        $find_finished_materials_old = FinishedModelMaterial::where('finished_model_id', $id)->get();
        if(count($find_finished_materials_old) > 0)
        {
            foreach ($find_finished_materials_old as $key => $find_finished_material_old) {
                $finished_material_old = FinishedModelMaterial::where('id', $find_finished_material_old->id)->first();
                $find_raw_material_old = RawMaterial::where('id', $finished_material_old->raw_material_id)->first();

                if($find_raw_material_old->current_stock < $finished_material_old->remain_qty)
                {
                    $cal_old = $finished_material_old->remain_qty - $find_raw_material_old->current_stock;
                    $message = 'Sorry! Not Enough '. $find_raw_material_old->name . ', Need ' . $cal_old . ' More Pcs';

                    return redirect()->back()->with('danger', $message);
                }
            }
        }

        $find_finished_materials = FinishedModelMaterial::where('finished_model_id', $id)->get();
        if(count($find_finished_materials) > 0)
        {
            foreach ($find_finished_materials as $key => $find_finished_material) {
                $finished_material = FinishedModelMaterial::where('id', $find_finished_material->id)->first();
                $find_raw_material = RawMaterial::where('id', $finished_material->raw_material_id)->first();

                $cal = $find_raw_material->current_stock - $finished_material->remain_qty;
                $temp_cal = $find_raw_material->temp_stock - $finished_material->remain_qty;

                $calculate = $finished_material->qty + $finished_material->remain_qty;

                $finished_material->update(['qty' => $calculate, 'remain_qty' => 0]);

                $find_raw_material->update(['current_stock'=>$cal, 'temp_stock'=>$temp_cal]);
            }
        }

        $find_details->update(['is_complete' => 1]);

        $message_success = 'Machine having Serial Number '.$find_details->serial_number.' has been marked Complete';

        return redirect()->back()->with('message',$message_success);
    }

    public function fetchInCompleteModelRecipe($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/in-complete-ups-model-list", "name" => "In-Complete UPS Models List"],["name" => "Add Remaining Inventory"]
        ];

        $model_id = $id;
        $materials = FinishedModelMaterial::where('finished_model_id', $id)->where('remain_qty', '!=', 0)->get();

        return view('user_portal.Inventory.model.in_complete_models.complete',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'materials'=>$materials,'model_id'=>$model_id]);
    }

    public function saveRemainigInventory(Request $request)
    {
        $qty_used_ids = $request->qty_used_ids;
        $qty_used = $request->qty_used;
        $model_id = $request->model_id;

        foreach ($qty_used_ids as $keyOne => $qty_used_id) {
            $find_material_old = FinishedModelMaterial::where('id', $qty_used_id)->first();
            $find_raw_material_old = RawMaterial::where('id', $find_material_old->raw_material_id)->first();

            if($find_raw_material_old->current_stock < $find_material_old->remain_qty)
            {
                $cal_old = $find_material_old->remain_qty - $find_raw_material_old->current_stock;
                $message = 'Sorry! Not Enough '. $find_raw_material_old->name . ', Need ' . $cal_old . ' More Pcs';

                return redirect()->back()->with('danger', $message);
            }
        }

        foreach ($qty_used_ids as $key => $qty_used_id) {
            $find_material = FinishedModelMaterial::where('id', $qty_used_id)->first();
            $find_raw_material = RawMaterial::where('id', $find_material->raw_material_id)->first();

            $cal = $find_raw_material->current_stock - $qty_used[$key];
            $temp_cal = $find_raw_material->temp_stock - $qty_used[$key];

            $calculate = $find_material->qty + $qty_used[$key];
            $remain_calculate = $find_material->remain_qty - $qty_used[$key];

            $find_material->update(['qty'=>$calculate,'remain_qty'=>$remain_calculate]);
            $find_raw_material->update(['current_stock'=>$cal, 'temp_stock'=>$temp_cal]);
        }

        $check_remain = FinishedModelMaterial::whereIn('id', $qty_used_ids)->where('remain_qty', '!=', 0)->get();
        if(count($check_remain) > 0)
        {
            return redirect()->route('in-complete-ups-model-list')->with('warning', 'Remaining Inventory has been marked Successfully!');
        }
        else
        {
            $find_model = FinishedModels::where('id', $model_id)->first();
            $find_model->update(['is_complete' => 1]);

            return redirect()->route('in-complete-ups-model-list')->with('message', 'Remaining Inventory has been added, Machine Marked Finished!');
        }

    }

    public function checkModelProduction($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/ups-model-list", "name" => "UPS Models List"],["name" => "Check Model Production"]
        ];
        $ups_model = UPSModel::where('id', $id)->first();
        return view('user_portal.Inventory.model.check',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'ups_model' => $ups_model]);
    }

    public function getModelProduction(Request $request)
    {
        $qty = $request->qty;
        $machine_id = $request->id;

        $getRecipes = ModelRecipe::where('model_id', $machine_id)->get();
        if(count($getRecipes) > 0)
        {
            $html = '';
            foreach($getRecipes as $recipe)
            {
                if($recipe->rawMaterial->current_stock < ($recipe->qty * $qty))
                {
                    $max_qty = $recipe->rawMaterial->current_stock;
                }
                else
                {
                    $max_qty = $recipe->qty * $qty;
                }
                $html .= '<div class="row">';
                $html .= '<div class="col-md-6">';
                $html .= '<div class="form-group">';
                $html .= '<label class="form-label" for="basic-default-name">Raw Material</label><br>';
                $html .= '<input type="text" disabled name="select_raw_item_old[]" class="form-control" placeholder="Material Name" value ="'. $recipe->rawMaterial->name .'">';
                $html .= '</div></div>';
                $html .= '<div class="col-md-6">';
                $html .= '<div class="form-group">';
                $html .= '<label class="form-label" for="basic-default-name">Raw Material Quantity</label>';
                $html .= '<input type="number" max="'. $max_qty .'" min="'. $recipe->qty * $qty .'" required name="qty_old[]" class="form-control" placeholder="Enter Material Quantity Used" value="'. $recipe->qty * $qty .'">';
                if($recipe->rawMaterial->current_stock >= ($recipe->qty * $qty))
                {
                    $actual_final_qty = $recipe->rawMaterial->current_stock - ($recipe->qty * $qty);
                    $html .= '<span style="color: green; font-size: 10px; font-weight: bold;">Now '. $actual_final_qty .' pcs were left in stock</span>';
                }
                else
                {
                    $actual_final_qty = $recipe->rawMaterial->current_stock - ($recipe->qty * $qty);
                    $html .= '<span style="color: red; font-size: 10px; font-weight: bold;">Need '. $actual_final_qty .' more pcs in stock</span>';
                }
                $html .= '</div></div>';
                $html .= '</div>';
            }
            $html .= '';

            $data = [
                'status' => 1,
                'data' => $html,
            ];

            return response()->json(['response' => $data]);
        }
        else
        {
            $data = [
                'status' => 0,
                'message' => 'Production Recipe Not Exists, please add Production Recipe First',
            ];

            return response()->json(['response' => $data]);
        }
    }

    public function addInCompleteUpsModelNew(Request $request)
    {
        $authUser = Auth::user()->id;

        $serial_number_reference = $request->serial_number_reference;
        $model_id = $request->model_id;
        $checkedArray = $request->checkedArray;

        $get_model_recipes = ModelRecipe::where('model_id', $model_id)->get();
        if(count($get_model_recipes) > 0)
        {
            foreach ($get_model_recipes as $key => $get_model_recipe) {

                $data_cal = $get_model_recipe->qty - $checkedArray[$key];

                $inventory = RawMaterial::where('id', $get_model_recipe->raw_material_id)->first();

                if ($inventory->current_stock < $data_cal) {

                    $calculate = $data_cal - $inventory->current_stock;

                    $status = 'NotEnoughInventory';
                    $message = 'Sorry! Not Enough '. $inventory->name . ', Need ' . $calculate . ' More Pcs';
                    $data = 0;

                    $response = [
                        'status' => $status,
                        'message' => $message,
                        'finished_model' => $data,
                    ];

                    return response()->json($response);
                }
                else
                {

                }
            }
        }
        else
        {
            $status = 'NoRecipe';
            $message = 'Sorry! No Production Recipe Added Yet, Add Recipe First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'finished_model' => $data,
            ];

            return response()->json($response);
        }

        $check_serial_number = FinishedModels::where('serial_number', $serial_number_reference)->first();

        if(!empty($check_serial_number))
        {
            $status = 'Already';
            $message = 'This Serial Number Has Already been Scanned';
            $data = 0;
        }
        else
        {
            $data = [
                'serial_number' => $serial_number_reference,
                'model_id' => $model_id,
                'created_by' => $authUser,
                'is_complete' => 0,
                'status' => 0,
            ];

            $model = FinishedModels::create($data);

            $get_model_recipes_new = ModelRecipe::where('model_id', $model_id)->get();
            if(count($get_model_recipes_new) > 0)
            {
                foreach ($get_model_recipes_new as $keyOne => $get_model_recipe_new) {

                    $data_cal_new = $get_model_recipe_new->qty - $checkedArray[$keyOne];

                    $inventory_new = RawMaterial::where('id', $get_model_recipe_new->raw_material_id)->first();

                    $final_value = $inventory_new->current_stock - $data_cal_new;
                    $final_temp_value = $inventory_new->temp_stock - $data_cal_new;

                    $remain_qty = $get_model_recipe_new->qty - $data_cal_new;

                    $inventory_new->update(['current_stock'=>$final_value, 'temp_stock'=>$final_temp_value]);

                    $material_data = [
                        'finished_model_id' => $model->id,
                        'raw_material_id' => $get_model_recipe_new->raw_material_id,
                        'qty' => $data_cal_new,
                        'remain_qty' => $remain_qty,
                        'status' => 1,
                    ];

                    FinishedModelMaterial::create($material_data);
                }
            }

            $latest_model = FinishedModels::with('modelDetail')->where('id', $model->id)->first();

            $status = 'Success';
            $message = 'Finished UPS Added Successfully';
            $data = $latest_model;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'finished_model' => $data,
        ];

        return response()->json($response);
    }

    public function markCompleteUpsModelNew($id)
    {
        $find_details = FinishedModels::where('id', $id)->first();

        $find_finished_materials_old = FinishedModelMaterial::where('finished_model_id', $id)->get();
        if(count($find_finished_materials_old) > 0)
        {
            foreach ($find_finished_materials_old as $key => $find_finished_material_old) {
                $finished_material_old = FinishedModelMaterial::where('id', $find_finished_material_old->id)->first();
                $find_raw_material_old = RawMaterial::where('id', $finished_material_old->raw_material_id)->first();

                if($find_raw_material_old->current_stock < $finished_material_old->remain_qty)
                {
                    $cal_old = $finished_material_old->remain_qty - $find_raw_material_old->current_stock;
                    $message = 'Sorry! Not Enough '. $find_raw_material_old->name . ', Need ' . $cal_old . ' More Pcs';

                    return redirect()->back()->with('danger', $message);
                }
            }
        }

        $find_finished_materials = FinishedModelMaterial::where('finished_model_id', $id)->get();
        if(count($find_finished_materials) > 0)
        {
            foreach ($find_finished_materials as $key => $find_finished_material) {
                $finished_material = FinishedModelMaterial::where('id', $find_finished_material->id)->first();
                $find_raw_material = RawMaterial::where('id', $finished_material->raw_material_id)->first();

                $cal = $find_raw_material->current_stock - $finished_material->remain_qty;
                $temp_cal = $find_raw_material->temp_stock - $finished_material->remain_qty;

                $calculate = $finished_material->qty + $finished_material->remain_qty;

                $finished_material->update(['qty' => $calculate, 'remain_qty' => 0]);

                $find_raw_material->update(['current_stock'=>$cal, 'temp_stock'=>$temp_cal]);
            }
        }

        $find_details->update(['is_complete' => 1]);

        $message_success = 'Machine having Serial Number '.$find_details->serial_number.' has been marked Complete';

        return redirect()->back()->with('message',$message_success);
    }

    public function fetchInCompleteModelRecipeNew($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/in-complete-ups-model-list", "name" => "In-Complete UPS Models List"],["name" => "Add Remaining Inventory"]
        ];

        $model_id = $id;
        $materials = FinishedModelMaterial::where('finished_model_id', $id)->where('remain_qty', '!=', 0)->get();

        return view('user_portal.Inventory.model.in_complete_models.complete',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'materials'=>$materials,'model_id'=>$model_id]);
    }

    public function saveRemainigInventoryNew(Request $request)
    {
        $qty_used_ids = $request->qty_used_ids;
        $qty_used = $request->qty_used;
        $model_id = $request->model_id;

        foreach ($qty_used_ids as $keyOne => $qty_used_id) {
            $find_material_old = FinishedModelMaterial::where('id', $qty_used_id)->first();
            $find_raw_material_old = RawMaterial::where('id', $find_material_old->raw_material_id)->first();

            $data_calculate = $find_material_old->remain_qty - $qty_used[$keyOne];

            if($find_raw_material_old->current_stock < $data_calculate)
            {
                $cal_old = $data_calculate - $find_raw_material_old->current_stock;
                $message = 'Sorry! Not Enough '. $find_raw_material_old->name . ', Need ' . $cal_old . ' More Pcs';

                return redirect()->back()->with('danger', $message);
            }
        }

        foreach ($qty_used_ids as $key => $qty_used_id) {
            $find_material = FinishedModelMaterial::where('id', $qty_used_id)->first();
            $find_raw_material = RawMaterial::where('id', $find_material->raw_material_id)->first();

            $data_cal = $find_material->remain_qty - $qty_used[$key];

            $cal = $find_raw_material->current_stock - $data_cal;
            $temp_cal = $find_raw_material->temp_stock - $data_cal;

            $calculate = $find_material->qty + $data_cal;
            $remain_calculate = $qty_used[$key];

            $find_material->update(['qty'=>$calculate,'remain_qty'=>$remain_calculate]);
            $find_raw_material->update(['current_stock'=>$cal, 'temp_stock'=>$temp_cal]);
        }

        $check_remain = FinishedModelMaterial::whereIn('id', $qty_used_ids)->where('remain_qty', '!=', 0)->get();
        if(count($check_remain) > 0)
        {
            return redirect()->route('in-complete-ups-model-list')->with('warning', 'Remaining Inventory has been marked Successfully!');
        }
        else
        {
            $find_model = FinishedModels::where('id', $model_id)->first();
            $find_model->update(['is_complete' => 1]);

            return redirect()->route('in-complete-ups-model-list')->with('message', 'Remaining Inventory has been added, Machine Marked Finished!');
        }

    }
}
