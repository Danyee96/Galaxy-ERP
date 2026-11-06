<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\RawMaterial;
use App\Models\TransitMethod;
use App\Models\RawMaterialInventory;
use App\Models\SemiRawMaterial;
use App\Models\FinishedGoods;
use App\Models\SemiRawMaterialRecipe;
use App\Models\FinishedGoodsRecipe;
use App\Models\SemiRawMaterialInventory;
use App\Models\FinishedGoodsInventory;
use App\Models\FinishedGoodsOut;
use App\Models\Company;
use App\Models\Power;
use Carbon\Carbon;
use Auth;

class RawMaterialController extends Controller
{
    public function rawMaterial()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Raw Material List"]
        ];
        $raw_materials = RawMaterial::all();
        return view('user_portal.Inventory.raw material.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    public function createRawMaterial()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add Raw Material"]
        ];
        return view('user_portal.Inventory.raw material.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveRawMaterial(Request $request)
    {
        $name = $request->material_name;
        $current_stock = $request->current_stock;

        $data = [
            'name' => $name,
            'current_stock' => $current_stock,
            'temp_stock' => $current_stock,
            'status' => 1,
        ];

        RawMaterial::create($data);

        return redirect()->route('raw-items-list')->with('message', 'Raw Item Added Successfully!');
    }

    public function editMaterial($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/raw-items-list", "name" => "Raw Material List"],["name" => "Edit"]
        ];
        $raw_material = RawMaterial::where('id', $id)->first();
        return view('user_portal.Inventory.raw material.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_material' => $raw_material]);
    }

    public function updateRawMaterial(Request $request)
    {
        $name = $request->material_name;
        $raw_material_id = $request->raw_material_id;

        $raw_material = RawMaterial::where('id', $raw_material_id)->first();

        $data = [
            'name' => $name,
        ];

        $raw_material->update($data);

        return redirect()->route('raw-items-list')->with('message', 'Raw Item Updated Successfully!');
    }

    public function addInventory($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/raw-items-list", "name" => "Raw Material List"],["name" => "Add Inventory"]
        ];
        $raw_material = RawMaterial::where('id', $id)->first();
        $transits = TransitMethod::all();
        return view('user_portal.Inventory.raw material.add-inventory',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_material' => $raw_material, 'transits' => $transits]);
    }

    public function saveInventory(Request $request)
    {
        $qty = $request->material_qty;
        $select_transit = $request->select_transit;
        $remarks = $request->remarks;
        $raw_material_id = $request->raw_material_id;
        $raw_material = RawMaterial::where('id', $raw_material_id)->first();
        $total_quantity = $raw_material->current_stock + $qty;
        $temp_qty = $raw_material->temp_stock + $qty;
        $transit_qty = $raw_material->transit_stock + $qty;
        if($select_transit == 1)
        {
            $raw_material->update(['current_stock' => $total_quantity, 'temp_stock' => $temp_qty]);

            $data = [
                'raw_material_id' => $raw_material_id,
                'transit_id' => $select_transit,
                'stock_in' => $qty,
                'remarks' => $remarks,
                'status' => 1,
                'entry_by' => Auth::user()->id,
                'received_by' => Auth::user()->id,
            ];

            RawMaterialInventory::create($data);
        }
        else
        {
            $raw_material->update(['temp_stock' => $temp_qty,'transit_stock' => $transit_qty]);

            $data = [
                'raw_material_id' => $raw_material_id,
                'transit_id' => $select_transit,
                'stock_in' => $qty,
                'remarks' => $remarks,
                'status' => 0,
                'entry_by' => Auth::user()->id,
            ];

            RawMaterialInventory::create($data);
        }

        return redirect()->route('viewInventory', $raw_material_id)->with('message', 'Raw Item Inventory Added Successfully!');
    }

    public function viewInventory($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/raw-items-list", "name" => "Raw Material List"],["name" => "View Inventory"]
        ];
        $raw_materials = RawMaterialInventory::where('raw_material_id', $id)->orderBy('created_at','DESC')->get();
        return view('user_portal.Inventory.raw material.view-inventory',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    //semi raw material

    public function semiRawMaterial()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Semi Raw Material List"]
        ];
        $raw_materials = SemiRawMaterial::all();
        return view('user_portal.Inventory.semi raw material.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    public function createSemiRawMaterial()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add Semi Raw Material"]
        ];
        return view('user_portal.Inventory.semi raw material.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveSemiRawMaterial(Request $request)
    {
        $name = $request->material_name;
        $current_stock = $request->current_stock;

        $data = [
            'name' => $name,
            'current_stock' => $current_stock,
            'temp_stock' => $current_stock,
            'status' => 1,
        ];

        SemiRawMaterial::create($data);

        return redirect()->route('semi-raw-items-list')->with('message', 'Raw Item Added Successfully!');
    }

    public function editSemiRawMaterial($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/semi-raw-items-list", "name" => "Semi Raw Material List"],["name" => "Edit"]
        ];
        $raw_material = SemiRawMaterial::where('id', $id)->first();
        return view('user_portal.Inventory.semi raw material.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_material' => $raw_material]);
    }

    public function updateSemiRawMaterial(Request $request)
    {
        $name = $request->material_name;
        $raw_material_id = $request->raw_material_id;

        $raw_material = SemiRawMaterial::where('id', $raw_material_id)->first();

        $data = [
            'name' => $name,
        ];

        $raw_material->update($data);

        return redirect()->route('semi-raw-items-list')->with('message', 'Raw Item Updated Successfully!');
    }

    //Finished Good material

    public function finishedGoodsList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Finished Goods List"]
        ];
        $raw_materials = FinishedGoods::all();
        return view('user_portal.Inventory.finished goods.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    public function createFinishedGoods()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Add Finished Goods"]
        ];
        return view('user_portal.Inventory.finished goods.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }

    public function saveFinishedGoods(Request $request)
    {
        $name = $request->material_name;
        $current_stock = $request->current_stock;

        $data = [
            'name' => $name,
            'current_stock' => $current_stock,
            'temp_stock' => $current_stock,
            'status' => 1,
        ];

        FinishedGoods::create($data);

        return redirect()->route('finished-goods-list')->with('message', 'Finished Goods Added Successfully!');
    }

    public function editFinishedGoods($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/finished-goods-list", "name" => "Finished Goods List"],["name" => "Edit"]
        ];
        $raw_material = FinishedGoods::where('id', $id)->first();
        return view('user_portal.Inventory.finished goods.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_material' => $raw_material]);
    }

    public function updateFinishedGoods(Request $request)
    {
        $name = $request->material_name;
        $raw_material_id = $request->raw_material_id;

        $raw_material = FinishedGoods::where('id', $raw_material_id)->first();

        $data = [
            'name' => $name,
        ];

        $raw_material->update($data);

        return redirect()->route('finished-goods-list')->with('message', 'Finished Goods Updated Successfully!');
    }

    //semi-raw-material recipe management

    public function addSemiRawMaterialRecipe($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/semi-raw-items-list", "name" => "Semi Raw Material List"],["name" => "Add Production Making"]
        ];
        $semi_raw_material = SemiRawMaterial::where('id', $id)->first();
        $raw_materials = RawMaterial::all();
        $recipes = SemiRawMaterialRecipe::where('semi_raw_material_id', $id)->get();
        return view('user_portal.Inventory.semi raw material.create-recipe',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'semi_raw_material' => $semi_raw_material, 'raw_materials' => $raw_materials,'recipes'=> $recipes]);
    }

    public function saveSemiRawMaterialRecipe(Request $request)
    {
        // dd($request->all());
        $menu_item = $request->menu_item_id;

        if($request->has('select_raw_item'))
        {
            $raw_materials = $request->select_raw_item;
            $raw_qty = $request->qty;

            foreach($raw_materials as $key => $raw_material)
            {
                $dateOne = [
                    'semi_raw_material_id' => $menu_item,
                    'raw_material_id' => $raw_materials[$key],
                    'qty' => $raw_qty[$key],
                ];

                SemiRawMaterialRecipe::create($dateOne);
            }
        }

        if($request->has('recipe_id'))
        {
            $recipe_id = $request->recipe_id;
            $raw_qty_old = $request->qty_old;

            foreach($recipe_id as $keyTwo => $recipe_ids)
            {
                $find_recipe = SemiRawMaterialRecipe::where('id', $recipe_ids)->first();
                $dateOne_old = [
                    'semi_raw_material_id' => $menu_item,
                    'qty' => $raw_qty_old[$keyTwo],
                ];

                $find_recipe->update($dateOne_old);
            }
        }

        return redirect()->route('semi-raw-items-list')->with('message', 'Production Recipe Added Successfully!');
    }

    //finished goods recipe management

    public function addFinishedGoodsRecipe($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/finished-goods-list", "name" => "Finished Goods List"],["name" => "Add Production Making"]
        ];
        $semi_raw_material = FinishedGoods::where('id', $id)->first();
        $raw_materials = RawMaterial::all();
        $semi_raw_materials = SemiRawMaterial::all();
        $recipes = FinishedGoodsRecipe::where('finished_good_id', $id)->get();
        return view('user_portal.Inventory.finished goods.create-recipe',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'semi_raw_material' => $semi_raw_material, 'raw_materials' => $raw_materials,'recipes'=> $recipes,'semi_raw_materials'=>$semi_raw_materials]);
    }

    public function saveFinishedGoodsRecipe(Request $request)
    {
        // dd($request->all());
        $menu_item = $request->menu_item_id;

        if($request->has('select_raw_item'))
        {
            $raw_materials = $request->select_raw_item;
            $raw_qty = $request->qty;

            foreach($raw_materials as $key => $raw_material)
            {
                $dateOne = [
                    'finished_good_id' => $menu_item,
                    'material_id' => $raw_materials[$key],
                    'material_type' => 'Raw',
                    'qty' => $raw_qty[$key],
                ];

                FinishedGoodsRecipe::create($dateOne);
            }
        }

        if($request->has('select_semi_raw_item'))
        {
            $semi_raw_materials = $request->select_semi_raw_item;
            $semi_qty = $request->semi_qty;

            foreach($semi_raw_materials as $key => $semi_raw_material)
            {
                $dateOne = [
                    'finished_good_id' => $menu_item,
                    'material_id' => $semi_raw_materials[$key],
                    'material_type' => 'Semi',
                    'qty' => $semi_qty[$key],
                ];

                FinishedGoodsRecipe::create($dateOne);
            }
        }

        if($request->has('recipe_id'))
        {
            $recipe_id = $request->recipe_id;
            $raw_qty_old = $request->qty_old;

            foreach($recipe_id as $keyTwo => $recipe_ids)
            {
                $find_recipe = FinishedGoodsRecipe::where('id', $recipe_ids)->first();
                $dateOne_old = [
                    'finished_good_id' => $menu_item,
                    'qty' => $raw_qty_old[$keyTwo],
                ];

                $find_recipe->update($dateOne_old);
            }
        }

        return redirect()->route('finished-goods-list')->with('message', 'Production Recipe Added Successfully!');
    }

    //semi finished goods production

    public function addSemiRawMaterialProduction($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/semi-raw-items-list", "name" => "Semi Raw Material List"],["name" => "Add Production Quantity"]
        ];
        $semi_raw_material = SemiRawMaterial::where('id', $id)->first();
        return view('user_portal.Inventory.semi raw material.production',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'semi_raw_material' => $semi_raw_material]);
    }

    public function getSemiRawMaterialProduction(Request $request)
    {
        $qty = $request->qty;
        $machine_id = $request->id;

        $getRecipes = SemiRawMaterialRecipe::where('semi_raw_material_id', $machine_id)->get();
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
                $html .= '<input type="hidden" class="form-control" name="raw_material_id[]" value="' . $recipe->raw_material_id . '">';
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

    public function saveSemiRawMaterialProduction(Request $request)
    {
        $machine_id = $request->machine_id;
        $machine_qty = $request->machine_qty;
        $created_by = Auth::user()->name;
        $remarks = 'Stock Added in Production by '. $created_by;

        $data = [
            'semi_raw_material_id' => $machine_id,
            'stock_in' => $machine_qty,
            'remarks' => $remarks,
            'transit_id' => 1,
            'status' => 1,
        ];

        SemiRawMaterialInventory::create($data);

        if($request->has('raw_material_id'))
        {
            $raw_material_id = $request->raw_material_id;
            $raw_qty_old = $request->qty_old;

            foreach($raw_material_id as $keyTwo => $raw_material)
            {
                $find_material = RawMaterial::where('id', $raw_material)->first();
                $current_stock = $find_material->current_stock - $raw_qty_old[$keyTwo];
                $temp_stock = $find_material->temp_stock - $raw_qty_old[$keyTwo];
                $dateOne_old = [
                    'current_stock' => $current_stock,
                    'temp_stock' => $temp_stock,
                ];

                $find_material->update($dateOne_old);
            }
        }

        $find_product = SemiRawMaterial::where('id', $machine_id)->first();
        $final_qty = $find_product->current_stock + $machine_qty;
        $final_temp_qty = $find_product->temp_stock + $machine_qty;

        $find_product->update(['current_stock'=>$final_qty, 'temp_stock'=>$final_temp_qty]);

        return redirect()->route('semi-raw-items-list')->with('message', 'Production Added Successfully!');
    }

    public function viewSemiRawMaterialProduction($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/semi-raw-items-list", "name" => "Semi Raw Material List"],["name" => "View Production Quantity"]
        ];

        $raw_materials = SemiRawMaterialInventory::where('semi_raw_material_id', $id)->orderBy('created_at','DESC')->get();
        return view('user_portal.Inventory.semi raw material.view-production',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    //finished goods production

    public function addFinishedGoodsProduction($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/finished-goods-list", "name" => "Finished Goods List"],["name" => "Add Production Quantity"]
        ];
        $finished_good = FinishedGoods::where('id', $id)->first();
        return view('user_portal.Inventory.finished goods.production',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'finished_good' => $finished_good]);
    }

    public function getFinishedGoodsProduction(Request $request)
    {
        $qty = $request->qty;
        $machine_id = $request->id;

        $getRecipes = FinishedGoodsRecipe::where('finished_good_id', $machine_id)->get();
        if(count($getRecipes) > 0)
        {
            $html = '';
            foreach($getRecipes as $recipe)
            {
                if($recipe->material_type == 'Raw')
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
                    $html .= '<input type="hidden" class="form-control" name="raw_material_id[]" value="' . $recipe->material_id . '">';
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
                else
                {
                    if($recipe->semiRawMaterial->current_stock < ($recipe->qty * $qty))
                    {
                        $max_qty = $recipe->semiRawMaterial->current_stock;
                    }
                    else
                    {
                        $max_qty = $recipe->qty * $qty;
                    }

                    $html .= '<div class="row">';
                    $html .= '<input type="hidden" class="form-control" name="semi_raw_material_id[]" value="' . $recipe->material_id . '">';
                    $html .= '<div class="col-md-6">';
                    $html .= '<div class="form-group">';
                    $html .= '<label class="form-label" for="basic-default-name">Raw Material</label><br>';
                    $html .= '<input type="text" disabled name="select_raw_item_old[]" class="form-control" placeholder="Material Name" value ="'. $recipe->semiRawMaterial->name .'">';
                    $html .= '</div></div>';
                    $html .= '<div class="col-md-6">';
                    $html .= '<div class="form-group">';
                    $html .= '<label class="form-label" for="basic-default-name">Raw Material Quantity</label>';
                    $html .= '<input type="number" max="'. $max_qty .'" min="'. $recipe->qty * $qty .'" required name="semi_qty_old[]" class="form-control" placeholder="Enter Material Quantity Used" value="'. $recipe->qty * $qty .'">';
                    if($recipe->semiRawMaterial->current_stock >= ($recipe->qty * $qty))
                    {
                        $actual_final_qty = $recipe->semiRawMaterial->current_stock - ($recipe->qty * $qty);
                        $html .= '<span style="color: green; font-size: 10px; font-weight: bold;">Now '. $actual_final_qty .' pcs were left in stock</span>';
                    }
                    else
                    {
                        $actual_final_qty = $recipe->semiRawMaterial->current_stock - ($recipe->qty * $qty);
                        $html .= '<span style="color: red; font-size: 10px; font-weight: bold;">Need '. $actual_final_qty .' more pcs in stock</span>';
                    }
                    $html .= '</div></div>';
                    $html .= '</div>';
                }
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

    public function saveFinishedGoodsProduction(Request $request)
    {
        $machine_id = $request->machine_id;
        $machine_qty = $request->machine_qty;
        $created_by = Auth::user()->name;
        $remarks = 'Stock Added in Production by '. $created_by;

        $data = [
            'finished_good_id' => $machine_id,
            'stock_in' => $machine_qty,
            'remarks' => $remarks,
            'transit_id' => 1,
            'status' => 1,
        ];

        FinishedGoodsInventory::create($data);

        if($request->has('raw_material_id'))
        {
            $raw_material_id = $request->raw_material_id;
            $raw_qty_old = $request->qty_old;

            foreach($raw_material_id as $keyTwo => $raw_material)
            {
                $find_material = RawMaterial::where('id', $raw_material)->first();
                $current_stock = $find_material->current_stock - $raw_qty_old[$keyTwo];
                $temp_stock = $find_material->temp_stock - $raw_qty_old[$keyTwo];
                $dateOne_old = [
                    'current_stock' => $current_stock,
                    'temp_stock' => $temp_stock,
                ];

                $find_material->update($dateOne_old);
            }
        }

        if($request->has('semi_raw_material_id'))
        {
            $semi_raw_material_id = $request->semi_raw_material_id;
            $semi_qty_old = $request->semi_qty_old;

            foreach($semi_raw_material_id as $keyTwo => $raw_material)
            {
                $find_semi_material = SemiRawMaterial::where('id', $raw_material)->first();
                $semi_current_stock = $find_semi_material->current_stock - $semi_qty_old[$keyTwo];
                $semi_temp_stock = $find_semi_material->temp_stock - $semi_qty_old[$keyTwo];
                $semi_dateOne_old = [
                    'current_stock' => $semi_current_stock,
                    'temp_stock' => $semi_temp_stock,
                ];

                $find_semi_material->update($semi_dateOne_old);
            }
        }

        $find_product = FinishedGoods::where('id', $machine_id)->first();
        $final_qty = $find_product->current_stock + $machine_qty;
        $final_temp_qty = $find_product->temp_stock + $machine_qty;

        $find_product->update(['current_stock'=>$final_qty, 'temp_stock'=>$final_temp_qty]);

        return redirect()->route('finished-goods-list')->with('message', 'Production Added Successfully!');
    }

    public function viewFinishedGoodsProduction($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/finished-goods-list", "name" => "Finished Goods List"],["name" => "View Production Quantity"]
        ];

        $raw_materials = FinishedGoodsInventory::where('finished_good_id', $id)->orderBy('created_at','DESC')->get();
        return view('user_portal.Inventory.finished goods.view-production',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    //finished good sale

    public function addFinishedGoodsSale()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/finished-goods-list", "name" => "Finished Goods List"],["name" => "Add Finished Goods Sale"]
        ];
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $finished_goods = FinishedGoods::all();
        $companies = Company::all();
        $powers = Power::all();
        return view('user_portal.Inventory.sale',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'finished_goods' => $finished_goods, 'companies' => $companies, 'powers' => $powers, 'today_date' => $today_date]);
    }

    public function saveFinishedGoodsSale(Request $request)
    {
        $finished_goods_id = $request->finished_goods_id;
        $company_id = $request->company_id;
        $customer_name = $request->customer_name;
        $sale_quantity = $request->sale_quantity;
        $sale_date = $request->sale_date;
        $power_id = $request->power_id;
        $model = $request->model;

        $find_product = FinishedGoods::where('id', $finished_goods_id)->first();
        if($find_product->current_stock > $sale_quantity)
        {
            $data = [
                'finished_good_id' => $finished_goods_id,
                'company_id' => $company_id,
                'customer_name' => $customer_name,
                'sale_quantity' => $sale_quantity,
                'sale_date' => $sale_date,
                'power_id' => $power_id,
                'model' => $model,
            ];

            FinishedGoodsOut::create($data);

            $final_qty = $find_product->current_stock - $sale_quantity;
            $final_temp_qty = $find_product->temp_stock - $sale_quantity;

            $find_product->update(['current_stock'=>$final_qty, 'temp_stock'=>$final_temp_qty]);

            return redirect()->route('finished-goods-list')->with('message', 'Production Sold Successfully!');
        }
        else
        {
            $message = 'Currently you have '.$find_product->current_stock . ' in your inventory, you can not order more than this quantity';
            return redirect()->back()->with('danger', $message);
        }

    }

    public function viewFinishedGoodsSale($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/finished-goods-list", "name" => "Finished Goods List"],["name" => "View Finished Goods Sale"]
        ];

        $finished_good_sales = FinishedGoodsOut::where('finished_good_id', $id)->orderBy('sale_date','DESC')->get();
        return view('user_portal.Inventory.finished goods.view-sale',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'finished_good_sales' => $finished_good_sales]);
    }

    //in transit inventory

    public function viewTransitInventory()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "View Transit Inventory"]
        ];
        $raw_materials = RawMaterialInventory::where('status', 0)->orderBy('created_at','DESC')->get();
        return view('user_portal.Inventory.raw material.view-transit-inventory',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'raw_materials' => $raw_materials]);
    }

    public function saveTransitInventory($id)
    {
        if (config('warehouses.enabled')) {
            $doc=RawMaterialInventory::findOrFail($id);
            $received=\DB::table('warehouse_transit_receipts')->where('document_type','raw')->where('document_id',$id)->sum('quantity');
            app(\App\Services\Warehouse\Inventory::class)->receiveTransit(['request_key'=>request()->warehouse_request_key,'document_type'=>'raw','document_id'=>$id,'quantity'=>(string)($doc->stock_in-$received),'location_id'=>(int)(request()->destination_location_id ?: \DB::table('warehouse_state')->where('key','default_location')->value('value')),'date'=>today()->toDateString()]);
            return redirect()->back()->with('message','Stock received into the selected warehouse location.');
        }

        $find_inventory = RawMaterialInventory::where('id',$id)->first();

        $raw_material_id = $find_inventory->raw_material_id;
        $qty = $find_inventory->stock_in;
        $raw_material = RawMaterial::where('id', $raw_material_id)->first();
        $total_quantity = $raw_material->current_stock + $qty;
        $transit_quantity = $raw_material->transit_stock - $qty;
        $raw_material->update(['current_stock' => $total_quantity, 'transit_stock' => $transit_quantity]);

        $find_inventory->update(['status' => 1, 'received_by' => Auth::user()->id]);

        return redirect()->back()->with('message', 'Stock Received Successfully!');
    }
}
