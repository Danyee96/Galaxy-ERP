<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\UPSModel;
use App\Models\ModelRecipe;
use App\Models\FinishedModels;
use App\Models\FinishedModelMaterial;
use App\Models\Products;
use App\Models\ProductInventory;
use App\Models\ProductCategory;
use App\Models\ProductAsset;
use App\Models\ExpenseAccount;
use App\Models\IncomeAccount;
use App\Models\Bundle;
use App\Models\Customer;
use App\Models\ProductEstimate;
use App\Models\EstimateValue;
use App\Models\ItemHistory;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use Carbon\Carbon;
use Auth;

class QuickBookController extends Controller
{
    public function productService(Request $request)
    {
        if(!empty($request->type))
        {
            $type = $request->type;
            $name = $request->name;
            $sku = $request->sku;
            $image = $request->image;
            $category = $request->category;
            $initial_qty = $request->initial_qty;
            $date = $request->date;
            $re_order = $request->re_order;
            $inventory_asset = $request->inventory_asset;
            $description = $request->description;
            $sale_price = $request->sale_price;
            $income_account = $request->income_account;
            $purchase_info = $request->purchase_info;
            $cost = $request->cost;
            $expense_account = $request->expense_account;

            $product_data = [
                'type' => $type,
                'name' => $name,
                'sku' => $sku,
                'image' => $image,
                'category' => $category,
                'initial_qty' => $initial_qty,
                'date' => $date,
                're_order' => $re_order,
                'inventory_asset' => $inventory_asset,
                'description' => $description,
                'sale_price' => $sale_price,
                'income_account' => $income_account,
                'purchase_info' => $purchase_info,
                'cost' => $cost,
                'expense_account' => $expense_account,
                'total_qty' => $initial_qty,
                'temp_qty' => $initial_qty,
                'sea_transit' => 0,
                'air_transit' => 0,
            ];

            Products::create($product_data);

            return redirect()->route('productService');
        }
        elseif(!empty($request->non_type))
        {
            $type = $request->non_type;
            $name = $request->non_name;
            $sku = $request->non_sku;
            $image = $request->non_image;
            $category = $request->non_category;
            $description = $request->non_description;
            $sale_price = $request->non_sale_price;
            $income_account = $request->non_income_account;

            $product_data = [
                'type' => $type,
                'name' => $name,
                'sku' => $sku,
                'image' => $image,
                'category' => $category,
                'description' => $description,
                'sale_price' => $sale_price,
                'income_account' => $income_account,
            ];

            Products::create($product_data);

            return redirect()->route('productService');
        }
        elseif(!empty($request->s_type))
        {
            $type = $request->s_type;
            $name = $request->s_name;
            $sku = $request->s_sku;
            $image = $request->s_image;
            $category = $request->s_category;
            $description = $request->s_description;
            $sale_price = $request->s_sale_price;
            $income_account = $request->s_income_account;

            $product_data = [
                'type' => $type,
                'name' => $name,
                'sku' => $sku,
                'image' => $image,
                'category' => $category,
                'description' => $description,
                'sale_price' => $sale_price,
                'income_account' => $income_account,
            ];

            Products::create($product_data);

            return redirect()->route('productService');
        }
        elseif(!empty($request->b_type))
        {
            $type = $request->b_type;
            $name = $request->b_name;
            $sku = $request->b_sku;
            $image = $request->b_image;
            $description = $request->b_description;

            $product_data = [
                'type' => $type,
                'name' => $name,
                'sku' => $sku,
                'image' => $image,
                'description' => $description,
            ];

            $product_create = Products::create($product_data);

            if($request->has('product'))
            {
                $bundles = $request->product;
                $quantity = $request->quantity;
                foreach ($bundles as $keyOne => $name) {
                    $dataOne = [
                        'bundle_id' => $product_create->id,
                        'product_id' => $bundles[$keyOne],
                        'quantity' => $quantity[$keyOne],
                        'status' => 1,
                    ];

                    Bundle::create($dataOne);
                }
            }

            return redirect()->route('productService');
        }

        if (isset($request->f_status)) {
            // Build dynamic query
            $query = Products::query();

            // Apply filters
            $query->where('status', $request->f_status);

            if ($request->f_type !== 'All') {
                $query->where('type', $request->f_type);
            }

            if (!empty($request->f_category)) {
                $query->whereIn('category', $request->f_category);
            }

            if ($request->f_stock === 'Out') {
                $query->where('total_qty', 0);
            } elseif ($request->f_stock === 'In') {
                $query->where('total_qty', '>', 0);
            } elseif ($request->f_stock === 'Low') {
                $query->whereColumn('total_qty', '<=', 're_order')->where('total_qty', '!=', 0);
            }

            // Get filtered products
            $products = $query->get();

            // Air & Sea transit counts (global, not filter dependent)
            $air_transit = Products::where('air_transit', '>', 0)->count();
            $sea_transit = Products::where('sea_transit', '>', 0)->count();

            $low_stock = Products::where('type', 'Inventory')
            ->whereRaw('CAST(total_qty AS UNSIGNED) != 0')
            ->whereRaw('CAST(total_qty AS UNSIGNED) <= CAST(re_order AS UNSIGNED)')
            ->where('status', 1)
            ->count();
            $out_of_stock = Products::where('total_qty', 0)->count();

            // Other variables
            $categories = ProductCategory::where('status',1)->get();
            $todayDate = Carbon::now('+5:00')->format('Y-m-d');

            return view('user_portal.New.products', [
                'products' => $products,
                'sea_transit' => $sea_transit,
                'air_transit' => $air_transit,
                'out_of_stock' => $out_of_stock,
                'low_stock' => $low_stock,
                'todayDate' => $todayDate,
                'categories' => $categories
            ]);
        }

        $air_transit = Products::where('air_transit', '>', 0)->count();
        $sea_transit = Products::where('sea_transit', '>', 0)->count();
        $check_item = Products::where('name', 'Transformer')->select('total_qty', 're_order', 'type')->first();
        $low_stock = Products::where('type', 'Inventory')
        ->whereRaw('CAST(total_qty AS UNSIGNED) != 0')
        ->whereRaw('CAST(total_qty AS UNSIGNED) <= CAST(re_order AS UNSIGNED)')
        ->where('status', 1)
        ->count();
        $out_of_stock = Products::where('total_qty', 0)->count();
        
        $products = Products::orderBy('category', 'DESC')->where('status', 1)->get();
        $categories = ProductCategory::where('status',1)->get();
        $todayDate = Carbon::now(+5)->format('Y-m-d');
        return view('user_portal.New.products', ['products' => $products, 'sea_transit' => $sea_transit, 'air_transit' => $air_transit, 'out_of_stock' => $out_of_stock, 'low_stock' => $low_stock, 'todayDate' => $todayDate, 'categories' => $categories]);
    }

    public function inTransitInventory(Request $request)
    {
        $request->validate(['transit_method' => 'nullable|integer|in:1,2,3', 'overdue' => 'nullable|boolean']);
        $query = ProductInventory::with(['productDetails', 'entryBy'])->whereIn('transit_id', [1, 2, 3])->where('status', 0);
        if ($request->filled('transit_method')) $query->where('transit_id', $request->transit_method);
        if ($request->boolean('overdue')) $query->overdue();
        $product_inventories = $query->get();
        $overdue_count = ProductInventory::overdue()->count();
        $air_transit = ProductInventory::where('transit_id', 2)->where('status', 0)->count();
        $sea_transit = ProductInventory::where('transit_id', 3)->where('status', 0)->count();
        return view('user_portal.New.transit', compact('sea_transit', 'air_transit', 'product_inventories', 'overdue_count'));
    }

    public function receivedTransitInventory()
    {
        $product_inventories = ProductInventory::whereIn('transit_id', [1,2,3])->where('status', 1)->get();
        $by_hand = ProductInventory::where('transit_id', 1)->where('status', 1)->count();
        $air_transit = ProductInventory::where('transit_id', 2)->where('status', 1)->count();
        $sea_transit = ProductInventory::where('transit_id', 3)->where('status', 1)->count();
        return view('user_portal.New.received-transit', ['sea_transit' => $sea_transit, 'air_transit' => $air_transit, 'product_inventories' => $product_inventories, 'by_hand' => $by_hand]);
    }

    public function saveTransitInventoryNew(Request $request)
    {
        $product_id = $request->product_id;
        $new_quantity = $request->new_quantity;
        $transit_method = $request->transit_method;
        $expected_date = $request->expected_date;
        $production_date = $request->production_date;

        $entryBy = Auth::user()->id;
        $status = 0;
        if($transit_method == 1)
        {
            $status = 1;
            $remaining_stock = 0;
            $received_stock = $new_quantity;
        }
        else
        {
            $status = 0;
            $remaining_stock = $new_quantity;
            $received_stock = 0;
        }

        $description = $new_quantity .' Items has been Added in Stock.';

        $transit_data = [
            'product_id' => $product_id,
            'transit_id' => $transit_method,
            'stock_in' => $new_quantity,
            'description' => $description,
            'status' => $status,
            'entry_by' => $entryBy,
            'expected_date' => $expected_date,
            'production_date' => $production_date,
            'remaining_stock' => $remaining_stock,
            'received_stock' => $received_stock,
        ];

        ProductInventory::create($transit_data);

        $fetch_product = Products::where('id', $product_id)->first();
        if($transit_method == 1)
        {
            $total_new_qty = $fetch_product->total_qty + $new_quantity;
            $temp_new_qty = $fetch_product->temp_qty + $new_quantity;
            $air_new_transit = $fetch_product->air_transit;
            $sea_new_transit = $fetch_product->sea_transit;
        }
        elseif($transit_method == 2)
        {
            $total_new_qty = $fetch_product->total_qty;
            $temp_new_qty = $fetch_product->temp_qty + $new_quantity;
            $air_new_transit = $fetch_product->air_transit + $new_quantity;
            $sea_new_transit = $fetch_product->sea_transit;
        }
        elseif($transit_method == 3)
        {
            $total_new_qty = $fetch_product->total_qty;
            $temp_new_qty = $fetch_product->temp_qty + $new_quantity;
            $air_new_transit = $fetch_product->air_transit;
            $sea_new_transit = $fetch_product->sea_transit + $new_quantity;
        }

        $fetch_product->update(['total_qty' => $total_new_qty, 'temp_qty' => $temp_new_qty, 'air_transit' => $air_new_transit, 'sea_transit' => $sea_new_transit]);

        return redirect()->back();
    }

    public function markReceivedTransit($id)
    {
        if (config('warehouses.enabled')) {
            $request = request();
            $document = ProductInventory::findOrFail($id);
            $quantity = ($document->stock_in - $document->received_stock);
            app(\App\Services\Warehouse\Inventory::class)->receiveTransit(['request_key'=>$request->warehouse_request_key, 'document_type'=>'product', 'document_id'=>$document->id, 'quantity'=>(string)$quantity, 'location_id'=>(int)($request->destination_location_id ?: \DB::table('warehouse_state')->where('key','default_location')->value('value')), 'date'=>today()->toDateString()]);
            return ($document->fresh()->status == 1 ? redirect()->route('receivedTransitInventory') : redirect()->back())->with('message','Stock received into the selected warehouse location.');
        }

        $receivedBy = Auth::user()->id;
        $find_inventory = ProductInventory::where('id', $id)->first();
        $find_inventory->update(['status' => 1, 'received_by' => $receivedBy]);
        $find_product = Products::where('id', $find_inventory->product_id)->first();

        if($find_inventory->transit_id == 2)
        {
            $total_new_qty = $find_product->total_qty + $find_inventory->stock_in;
            $air_new_transit = $find_product->air_transit - $find_inventory->stock_in;
            $sea_new_transit = $find_product->sea_transit;
        }
        elseif($find_inventory->transit_id == 3)
        {
            $total_new_qty = $find_product->total_qty + $find_inventory->stock_in;
            $air_new_transit = $find_product->air_transit;
            $sea_new_transit = $find_product->sea_transit - $find_inventory->stock_in;
        }

        $find_product->update(['total_qty' => $total_new_qty, 'air_transit' => $air_new_transit, 'sea_transit' => $sea_new_transit]);

        return redirect()->route('receivedTransitInventory');
    }

    public function markReceivedTransitNew(Request $request)
    {
        if (config('warehouses.enabled')) {
            $request = request();
            $document = ProductInventory::findOrFail($request->product_id);
            $quantity = $request->new_quantity;
            app(\App\Services\Warehouse\Inventory::class)->receiveTransit(['request_key'=>$request->warehouse_request_key, 'document_type'=>'product', 'document_id'=>$document->id, 'quantity'=>(string)$quantity, 'location_id'=>(int)($request->destination_location_id ?: \DB::table('warehouse_state')->where('key','default_location')->value('value')), 'date'=>today()->toDateString()]);
            return ($document->fresh()->status == 1 ? redirect()->route('receivedTransitInventory') : redirect()->back())->with('message','Stock received into the selected warehouse location.');
        }

        // dd($request->all());
        $receivedBy = Auth::user()->id;
        $id = $request->product_id;
        $new_quantity = $request->new_quantity;
        $find_inventory = ProductInventory::where('id', $id)->first();
        $received_stock = $find_inventory->received_stock + $new_quantity;
        $remaining_stock = $find_inventory->remaining_stock - $new_quantity;

        if($remaining_stock == 0) {
            $find_inventory->update(['status' => 1, 'received_by' => $receivedBy, 'received_stock' => $received_stock, 'remaining_stock' => $remaining_stock]);
        }
        else {
            $find_inventory->update(['received_stock' => $received_stock, 'remaining_stock' => $remaining_stock]);
        }
        $find_product = Products::where('id', $find_inventory->product_id)->first();

        if($find_inventory->transit_id == 2)
        {
            $total_new_qty = $find_product->total_qty + $new_quantity;
            $air_new_transit = $find_product->air_transit - $new_quantity;
            $sea_new_transit = $find_product->sea_transit;
        }
        elseif($find_inventory->transit_id == 3)
        {
            $total_new_qty = $find_product->total_qty + $new_quantity;
            $air_new_transit = $find_product->air_transit;
            $sea_new_transit = $find_product->sea_transit - $new_quantity;
        }

        $find_product->update(['total_qty' => $total_new_qty, 'air_transit' => $air_new_transit, 'sea_transit' => $sea_new_transit]);

        if($remaining_stock == 0) {
            // If all items received, redirect to received inventory page
            return redirect()->route('receivedTransitInventory');
        }
        else {
            // If some items are still remaining, stay on the same page
            return redirect()->back();
        }
    }

    public function saveProductCategory(Request $request)
    {
        $data = ['name' => $request->name];

        ProductCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category added successfully!',
        ], 200);
    }

    public function getCategory()
    {
        try {
            // Fetch all categories from the database
            $categories = ProductCategory::select('id', 'name','status')->where('status',1)->get();

            // Return the categories as JSON
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function saveProductAsset(Request $request)
    {
        $data = ['name' => $request->name];

        ProductAsset::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Assets added successfully!',
        ], 200);
    }

    public function getAsset()
    {
        try {
            // Fetch all categories from the database
            $categories = ProductAsset::select('id', 'name')->get();

            // Return the categories as JSON
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch assets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function saveIncomeAccount(Request $request)
    {
        $data = ['name' => $request->name];

        IncomeAccount::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Income Account added successfully!',
        ], 200);
    }

    public function getIncomeAccount()
    {
        try {
            // Fetch all categories from the database
            $categories = IncomeAccount::select('id', 'name')->get();

            // Return the categories as JSON
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch Income Account',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function saveExpenseAccount(Request $request)
    {
        $data = ['name' => $request->name];

        ExpenseAccount::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Expense Account added successfully!',
        ], 200);
    }

    public function getExpenseAccount()
    {
        try {
            // Fetch all categories from the database
            $categories = ExpenseAccount::select('id', 'name')->get();

            // Return the categories as JSON
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch Expense Account',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductService()
    {
        try {
            // Fetch all categories from the database
            $categories = Products::select('id', 'name')->where('status',1)->get();

            // Return the categories as JSON
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch Product and Services',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCustomer()
    {
        $customers = Customer::all();
        return view('user_portal.New.customers', ['customers' => $customers]);
    }

    public function saveCustomer(Request $request)
    {
        $data = [
            'title' => $request->title,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'display_name' => $request->display_name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'fax' => $request->fax,
            'other' => $request->other,
            'website' => $request->website,
            'street_address_one' => $request->street_address_one,
            'street_address_two' => $request->street_address_two,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method,
            'terms' => $request->terms,
            'delivery_option' => $request->delivery_option,
            'language' => $request->language,
            'taxes' => $request->taxes,
            'opening_balance' => $request->opening_balance,
            'date' => $request->date,
        ];

        Customer::create($data);

        return redirect()->route('getCustomer');
    }

    public function productEstimates()
    {
        $filters = request()->validate(['search' => 'nullable|string|max:255', 'kind' => 'nullable|in:planning,legacy']);
        $query = ProductEstimate::with(['systems', 'bundleDetails', 'estimateDetails.productDetails'])->where('status', 1);
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) { $q->where('title', 'like', '%'.$filters['search'].'%')->orWhere('number', 'like', '%'.$filters['search'].'%'); });
        }
        if (($filters['kind'] ?? '') === 'planning') $query->whereNotNull('planning_version');
        if (($filters['kind'] ?? '') === 'legacy') $query->whereNull('planning_version');
        return view('user_portal.New.estimates', ['datas' => $query->latest('id')->paginate(25)->withQueryString()]);
    }

    public function createProductEstimates()
    {
        if (config('warehouses.enabled')) abort_unless(in_array((int) Auth::user()->role_id,[1,4,5],true),403);
        $pageConfigs = ['pageHeader' => false];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/product-estimates", "name" => "Product Estimates List"],["name" => "Check Product Estimate"]
        ];

        $number = 1;
        $data = ProductEstimate::orderBy('id','DESC')->first();
        if(!empty($data))
        {
            $estimate_number = $data->number + $number;
        }
        else
        {
            $estimate_number = 1000 + $number;
        }

        $all_products = Products::where('type', 'Bundle')->where('status',1)->orderBy('name')->get(['id', 'name', 'sku']);
        return view('user_portal.New.create-estimate',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'all_products' => $all_products, 'estimate_number' => $estimate_number]);
    }

    public function getProductEstimates(Request $request)
    {
        $qty = $request->qty;
        $machine_id = $request->id;

        $getRecipes = Bundle::where('bundle_id', $machine_id)->get();
        if(count($getRecipes) > 0)
        {
            $html = '';
            foreach($getRecipes as $recipe)
            {
                if($recipe->productDetails->temp_qty < ($recipe->quantity * $qty))
                {
                    $max_qty = $recipe->productDetails->temp_qty;
                }
                else
                {
                    $max_qty = $recipe->quantity * $qty;
                }
                $html .= '<div class="row">';
                $html .= '<div class="col-md-6">';
                $html .= '<div class="form-group">';
                $html .= '<label class="form-label" for="basic-default-name">Inventory Name</label><br>';
                $html .= '<input type="text" disabled name="select_raw_item_old[]" class="form-control" placeholder="Material Name" value ="'. $recipe->productDetails->name .'">';
                $html .= '</div></div>';
                $html .= '<div class="col-md-6">';
                $html .= '<div class="form-group">';
                $html .= '<label class="form-label" for="basic-default-name">Inventory Quantity</label>';
                $html .= '<input type="number" disabled max="'. $max_qty .'" min="'. $recipe->quantity * $qty .'" required name="qty_old[]" class="form-control" placeholder="Enter Material Quantity Used" value="'. $recipe->quantity * $qty .'">';
                if($recipe->productDetails->temp_qty >= ($recipe->quantity * $qty))
                {
                    $actual_final_qty = $recipe->productDetails->temp_qty - ($recipe->quantity * $qty);
                    $html .= '<span style="color: green; font-size: 10px; font-weight: bold;">After Saving this Estimation, '. $actual_final_qty .' Pcs will be left in stock</span>';
                }
                else
                {
                    $actual_final_qty = $recipe->productDetails->temp_qty - ($recipe->quantity * $qty);
                    $html .= '<span style="color: red; font-size: 10px; font-weight: bold;">You have less pcs in stock, You need '. $actual_final_qty .' more pcs to complete this product</span>';
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
                'message' => 'Bundle Recipe Not Exists, please add Bundle Recipe First',
            ];

            return response()->json(['response' => $data]);
        }
    }

    public function saveProductEstimates(Request $request)
    {
        $input = $request->validate(['id' => 'required|integer|min:1', 'qty' => 'required|integer|min:1|max:1000000', 'title' => 'required|string|max:255']);
        try {
            $estimate = app(\App\Services\Estimation\ProductionEstimationPlanner::class)->saveLegacy($input);
            return response()->json(['response' => ['status' => 1, 'message' => 'Estimation Saved Successfully!', 'number' => $estimate->number, 'id' => $estimate->id]]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['response' => ['status' => 0, 'message' => $e->getMessage()]]);
        }
    }

    public function SingleEstimateDownload($id)
    {
        abort_unless(in_array((int) Auth::user()->role_id, [1, 4, 5], true), 403);
        $estimate = ProductEstimate::where('id', $id)->with(['estimateDetails.productDetails'])->firstOrFail();
        if ($estimate->planning_version) {
            abort_unless(in_array((int) Auth::user()->role_id, [1, 4, 5], true), 403);
            return view('user_portal.estimation.print', ['estimate' => $estimate, 'plan' => $estimate->planningSnapshot()]);
        }
        $estimate->estimateDetails = $estimate->estimateDetails->sortBy(function ($detail) {
            return $detail->productDetails->category;
        });
        return view('user_portal.New.estimate-download', compact('estimate'));
    }

    public function productEstimatesCalculation()
    {
        // Fetch inventory items from Products
        $inventoryItems = Products::where('type', 'Inventory')->get();

        // Fetch required quantities and estimates for each inventory item
        $inventoryWithEstimates = $inventoryItems->map(function ($item) {
            // Filter estimates with `status` = 1 in ProductEstimate
            $estimates = $item->estimateValues->filter(function ($estimate) {
                $productEstimate = ProductEstimate::find($estimate->estimate_id); // Fetch ProductEstimate details
                return $productEstimate && $productEstimate->status === 1;
            })->map(function ($estimate) {
                $productEstimate = ProductEstimate::find($estimate->estimate_id); // Fetch ProductEstimate details
                return [
                    'number' => $productEstimate->number ?? 'N/A', // Get estimate number
                    'title' => $productEstimate->title ?? 'N/A',   // Get estimate title
                    'quantity_needed' => $estimate->quantity_needed,
                    'quantity_in_stock' => $estimate->quantity_in_stock,
                ];
            });

            // Skip items with no valid estimates
            if ($estimates->isEmpty()) {
                return null;
            }

            return [
                'name' => $item->name,
                'category' => $item->category,
                'sku' => $item->sku,
                'quantity_in_stock' => $item->total_qty,
                'total_needed' => $estimates->sum('quantity_needed'), // Total needed
                'total_in_stock' => $estimates->sum('quantity_in_stock'), // Total Stock
                'remaining_stock' => $item->temp_qty, // Remaining Stock
                'sea_transit' => $item->sea_transit, // Sea Transit
                'air_transit' => $item->air_transit, // Air Transit
                'estimates' => $estimates, // Valid estimates
            ];
        })->filter(); // Remove null entries (items with no valid estimates)

        return view('user_portal.New.estimates-calculation', ['inventoryWithEstimates' => $inventoryWithEstimates]);
    }

    public function downloadProductEstimatesCalculation()
    {
        // Fetch inventory items from Products
        $inventoryItems = Products::where('type', 'Inventory')->get();

        // Fetch required quantities and estimates for each inventory item
        $inventoryWithEstimates = $inventoryItems->map(function ($item) {
            // Filter estimates with `status` = 1 in ProductEstimate
            $estimates = $item->estimateValues->filter(function ($estimate) {
                $productEstimate = ProductEstimate::find($estimate->estimate_id); // Fetch ProductEstimate details
                return $productEstimate && $productEstimate->status === 1;
            })->map(function ($estimate) {
                $productEstimate = ProductEstimate::find($estimate->estimate_id); // Fetch ProductEstimate details
                return [
                    'number' => $productEstimate->number ?? 'N/A', // Get estimate number
                    'title' => $productEstimate->title ?? 'N/A',   // Get estimate title
                    'quantity_needed' => $estimate->quantity_needed,
                    'quantity_in_stock' => $estimate->quantity_in_stock,
                ];
            });

            // Skip items with no valid estimates
            if ($estimates->isEmpty()) {
                return null;
            }

            return [
                'name' => $item->name,
                'category' => $item->category,
                'sku' => $item->sku,
                'quantity_in_stock' => $item->total_qty,
                'total_needed' => $estimates->sum('quantity_needed'), // Total needed
                'total_in_stock' => $estimates->sum('quantity_in_stock'), // Total Stock
                'remaining_stock' => $item->temp_qty, // Remaining Stock
                'sea_transit' => $item->sea_transit, // Sea Transit
                'air_transit' => $item->air_transit, // Air Transit
                'estimates' => $estimates, // Valid estimates
            ];
        })->filter(); // Remove null entries (items with no valid estimates)

        return view('user_portal.New.download-calculation', ['inventoryWithEstimates' => $inventoryWithEstimates]);
    }

    public function deleteEstimate($id)
    {
        $find_estimate = ProductEstimate::where('id', $id)->first();
        abort_if($find_estimate && $find_estimate->planning_version, 405, 'Use the production-plan archive action.');

        $find_estimate_values = EstimateValue::where('estimate_id', $find_estimate->id)->get();
        if(count($find_estimate_values) > 0)
        {
            foreach($find_estimate_values as $dd)
            {
                $find_product = Products::where('id', $dd->product_id)->first();
                $temp_qty = $find_product->temp_qty + $dd->quantity_in_stock;

                $find_product->update(['temp_qty' => $temp_qty]);
            }
        }

        $find_estimate->update(['status' => 0]);

        return redirect()->back();
    }

    public function getInvoice(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255', 'invoice_number' => 'nullable|string|max:100',
            'invoice_name' => 'nullable|string|max:255',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d'.($request->filled('date_from') ? '|after_or_equal:date_from' : ''),
            'sort' => 'nullable|in:date,number,title,quantity', 'direction' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
        ]);
        // This issuance ledger stores quantities only; it has no monetary/payment/customer fields.
        foreach (['customer', 'status', 'payment_status', 'amount_min', 'amount_max'] as $unsupported) {
            if ($request->filled($unsupported)) throw \Illuminate\Validation\ValidationException::withMessages([$unsupported => 'This invoice ledger does not record customer links, monetary amounts, or payment statuses.']);
        }
        $query = Invoice::query();
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%$search%")
                    ->orWhere('invoice_name', 'like', "%$search%")
                    ->orWhereHas('invoiceDetails.productDetails', function ($products) use ($search) {
                        $products->where('name', 'like', "%$search%")->orWhere('sku', 'like', "%$search%")->orWhere('id', 'like', "%$search%");
                    });
            });
        }
        foreach (['invoice_number', 'invoice_name'] as $field) {
            if (isset($filters[$field]) && trim($filters[$field]) !== '') $query->where($field, 'like', '%'.trim($filters[$field]).'%');
        }
        if (!empty($filters['date_from'])) $query->where('created_at', '>=', $filters['date_from'].' 00:00:00');
        if (!empty($filters['date_to'])) $query->where('created_at', '<', Carbon::parse($filters['date_to'])->addDay()->format('Y-m-d').' 00:00:00');
        $summary = (clone $query)->selectRaw('COUNT(*) as invoice_count, COALESCE(SUM(total_count), 0) as item_count')->first();
        $sortColumns = ['date' => 'created_at', 'number' => 'invoice_number', 'title' => 'invoice_name', 'quantity' => 'total_count'];
        $query->orderBy($sortColumns[$filters['sort'] ?? 'date'], $filters['direction'] ?? 'desc')->orderBy('id', $filters['direction'] ?? 'desc');
        $datas = $query->with(['invoiceDetails.productDetails'])->paginate(25)->appends(collect($filters)->except('page')->all());
        $lastInvoice = Invoice::orderBy('id', 'DESC')->first(['invoice_number']);
        $invoice_number = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1001;
        $viewData = compact('invoice_number', 'datas', 'search', 'summary', 'filters');
        $viewData['hasInvoices'] = $lastInvoice !== null;
        if ($request->ajax()) return view('user_portal.New.invoice-results', $viewData);
        return view('user_portal.New.invoice-new', $viewData);
    }

    public function saveInvoice(Request $request)
    {
        $created_by = Auth::user()->id;
        $invoice_name = $request->invoice_name;
        $invoice_number = $request->invoice_number;
        $total_count = $request->total_count;

        $invoice_data = [
            'invoice_name' => $invoice_name,
            'invoice_number' => $invoice_number,
            'total_count' => $total_count,
            'created_by' => $created_by,
        ];

        $invoice_create = Invoice::create($invoice_data);

        if($request->has('product'))
        {
            $bundles = $request->product;
            $quantity = $request->quantity;
            foreach ($bundles as $keyOne => $name) {
                $check_product = Products::where('id', $bundles[$keyOne])->first();
                {
                    if($check_product->type == 'Bundle')
                    {
                        $get_bundle_details = Bundle::where('bundle_id', $bundles[$keyOne])->get();
                        foreach($get_bundle_details as $bb)
                        {
                            $final_qty = $quantity[$keyOne] * $bb->quantity;

                            $dataOne = [
                                'invoice_id' => $invoice_create->id,
                                'product_id' => $bb->product_id,
                                'product_qty' => $final_qty,
                            ];

                            InvoiceDetails::create($dataOne);

                            $find_product = Products::where('id', $bb->product_id)->first();
                            $total_new_qty = $find_product->total_qty - $final_qty;

                            $find_product->update(['total_qty' => $total_new_qty]);

                            $description = $final_qty .' Items has been Issued Against Invoice Number '.$invoice_number.' for Bundle Name "'. $check_product->name.'"';

                            $transit_data = [
                                'product_id' => $bb->product_id,
                                'transit_id' => 4,
                                'stock_in' => (-1) * $final_qty,
                                'description' => $description,
                                'status' => 1,
                                'entry_by' => $created_by,
                            ];

                            ProductInventory::create($transit_data);
                        }
                    }
                    else
                    {
                        $dataOne = [
                            'invoice_id' => $invoice_create->id,
                            'product_id' => $bundles[$keyOne],
                            'product_qty' => $quantity[$keyOne],
                        ];

                        InvoiceDetails::create($dataOne);

                        $find_product = Products::where('id', $bundles[$keyOne])->first();
                        $total_new_qty = $find_product->total_qty - $quantity[$keyOne];

                        $find_product->update(['total_qty' => $total_new_qty]);

                        $description = $quantity[$keyOne] .' Items has been Issued Against Invoice Number '.$invoice_number;

                        $transit_data = [
                            'product_id' => $bundles[$keyOne],
                            'transit_id' => 4,
                            'stock_in' => (-1) * $quantity[$keyOne],
                            'description' => $description,
                            'status' => 1,
                            'entry_by' => $created_by,
                        ];

                        ProductInventory::create($transit_data);
                    }
                }
            }
        }

        return redirect()->back();
    }

    public function SingleInvoiceDownload($id)
    {
        $data = Invoice::where('id', $id)->first();
        return view('user_portal.New.invoice-download',compact('data'));
    }

    public function getInvoiceProductService()
    {
        try {
            // Fetch all categories from the database
            $categories = Products::select('id', 'name', 'sku')->whereIn('type', ['Inventory','Bundle'])->orderBy('type', 'ASC')->where('status', 1)->get();

            // Return the categories as JSON
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch Product and Services',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductQuantity($id)
    {
        try {
            // Fetch the product by ID
            $product = Products::select('id', 'total_qty')->where('id', $id)->where('status', 1)->first();

            if (!$product) {
                return response()->json([
                    'error' => 'Product not found'
                ], 404);
            }

            // Return the quantity as JSON
            return response()->json([
                'quantity' => $product->total_qty
            ], 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'error' => 'Unable to fetch product quantity',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function inventoryReport($id)
    {
        $data = Products::where('id', $id)->first();
        return view('user_portal.New.inventory-report-download',compact('data'));
    }

    public function updateProductInfo(Request $request)
    {
        $product_value = $request->product_value;
        $product_name = $request->product_name;
        $product_sku = $request->product_sku;
        $product_reorder = $request->product_reorder;
        $product_category = $request->product_category;

        $find_product = Products::where('id', $product_value)->first();
        $data = [
            'name' => $product_name,
            'sku' => $product_sku,
            're_order' => $product_reorder,
            'category' => $product_category,
        ];

        $find_product->update($data);

        return redirect()->back();
    }

    public function deleteProduct($id)
    {
        $find_product = Products::where('id', $id)->first();
        $find_product->update(['status' => 0]);

        return redirect()->back();
    }

    public function activeProduct($id)
    {
        $find_product = Products::where('id', $id)->first();
        $find_product->update(['status' => 1]);

        return redirect()->back();
    }

    //low stock
    public function lowStockProducts(Request $request)
    {
        $name = 'Low Stock Products';
        $products = Products::where('type', 'Inventory')
            ->where('status', 1)
            ->where(function($query) {
                $query->whereRaw('CAST(total_qty AS UNSIGNED) != 0')
                      ->whereRaw('CAST(total_qty AS UNSIGNED) <= CAST(re_order AS UNSIGNED)')
                      ->orWhere('total_qty', '<', 0);
            })
            ->get();
        return view('user_portal.New.low-stock', ['products' => $products, 'name' => $name]);
    }

    public function outStockProducts(Request $request)
    {
        $name = 'Out of Stock Products';
        $products = Products::where('status', 1)
            ->where(function($query) {
                $query->where('total_qty', 0)
                      ->orWhere('total_qty', '<', 0);
            })
            ->get();
        return view('user_portal.New.low-stock', ['products' => $products, 'name' => $name]);
    }

    public function productCategories(Request $request)
    {
        if (isset($request->f_status)) {
            // Build dynamic query
            $query = ProductCategory::query();

            // Apply filters
            $query->where('status', $request->f_status);

            // Get filtered categories
            $categories = $query->get();

            return view('user_portal.New.categories', ['categories' => $categories]);
        }

        $categories = ProductCategory::where('status', 1)->get();
        return view('user_portal.New.categories', ['categories' => $categories]);
    }

    public function deleteCategory($id)
    {
        $find_category = ProductCategory::where('id', $id)->first();
        $find_category->update(['status' => 0]);

        return redirect()->back();
    }

    public function activeCategory($id)
    {
        $find_category = ProductCategory::where('id', $id)->first();
        $find_category->update(['status' => 1]);

        return redirect()->back();
    }

    public function editBundleProducts($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/products", "name" => "Products List"],["name" => "Edit Bundle Products"]
        ];

        $bundle = Products::where('id', $id)->first();
        $all_products = Products::where('type', 'Inventory')->where('status',1)->get();
        $bundle_recipes = Bundle::where('bundle_id', $id)->get();

        return view('user_portal.New.edit-bundle-products',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs, 'bundle' => $bundle, 'all_products' => $all_products, 'bundle_recipes' => $bundle_recipes]);
    }

    public function updateBundleProducts(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'bundle_name' => 'required|string|max:255',
            'product' => 'required|array|min:1',
            'product.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
        ]);

        // Find the bundle
        $bundle = Products::where('id', $id)->where('type', 'Bundle')->firstOrFail();

        // Update bundle basic information
        $bundle_data = [
            'name' => $request->bundle_name,
            'sku' => $request->bundle_sku,
        ];

        $bundle->update($bundle_data);

        // Delete existing bundle recipes
        Bundle::where('bundle_id', $id)->delete();

        // Create new bundle recipes
        if ($request->has('product')) {
            $products = $request->product;
            $quantities = $request->quantity;
            
            foreach ($products as $key => $product_id) {
                if (!empty($product_id) && !empty($quantities[$key])) {
                    $bundle_recipe_data = [
                        'bundle_id' => $id,
                        'product_id' => $product_id,
                        'quantity' => $quantities[$key],
                        'status' => 1,
                    ];

                    Bundle::create($bundle_recipe_data);
                }
            }
        }

        return redirect()->route('productService')->with('success', 'Bundle updated successfully!');
    }
}
