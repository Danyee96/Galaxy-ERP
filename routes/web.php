<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\IconsController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ExComponentController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ExtensionsController;
use App\Http\Controllers\LanguageController;

// My Controllers
use App\Http\Controllers\user_portal\LoginController;
use App\Http\Controllers\user_portal\IndexController;
use App\Http\Controllers\user_portal\UserController;
use App\Http\Controllers\user_portal\RawMaterialController;
use App\Http\Controllers\user_portal\UnitController;
use App\Http\Controllers\user_portal\SerializationController;
use App\Http\Controllers\user_portal\ModelController;
use App\Http\Controllers\user_portal\MachineTestingController;
use App\Http\Controllers\user_portal\QuickBookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/********************* My Routes  *************************/


/*clear cache and config*/
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    dump('Cache Clear!');
    Artisan::call('config:clear');
    dump('Config Clear!');
    Artisan::call('route:clear');
    dump('Route Clear!');
    
    Artisan::call('view:clear');
    dump('View Clear!');
    
    return back();
});

Route::get('/migrate', function () {
        
    Artisan::call('migrate');
    dump('Migrate');
    return back();
});

Route::get('/login', [LoginController::class, 'getLogin'])->name('getLogin')->middleware('guest');
Route::post('userLogin', [LoginController::class, 'userLogin'])->name('userlogin'); 
Route::get('/', [IndexController::class, 'index'])->name('index')->middleware('auth');
Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard',[IndexController::class, 'index'])->name('dashboard');

    // User Management Routes
    Route::get('/create-user', [UserController::class, 'createUser'])->name('createUser');
    Route::post('/save-users', [UserController::class, 'saveUser'])->name('saveUser');
    Route::get('/users', [UserController::class, 'UserList'])->name('UserList');
    Route::get('/edit-user/{id}', [UserController::class, 'editUser'])->name('editUser');
    Route::post('/update-user/{id}', [UserController::class, 'updateUser'])->name('updateUser');
    Route::get('/edit-user-password/{id}', [UserController::class, 'updatePassword'])->name('edit-user-password');
    Route::post('/update-user-password', [UserController::class, 'updateUserPassword'])->name('updateUserPassword');

    //Company
    Route::get('/create-company', [UserController::class, 'createCompany'])->name('createCompany');
    Route::post('/save-company', [UserController::class, 'saveCompany'])->name('saveCompany');
    Route::get('/company-list', [UserController::class, 'CompanyList'])->name('CompanyList');
    Route::get('/edit-company/{id}', [UserController::class, 'editCompany'])->name('editCompany');
    Route::post('/update-company/{id}', [UserController::class, 'updateCompany'])->name('updateCompany');

    //Power
    Route::get('/create-power', [UserController::class, 'createPower'])->name('createPower');
    Route::post('/save-power', [UserController::class, 'savePower'])->name('savePower');
    Route::get('/power-list', [UserController::class, 'PowerList'])->name('PowerList');
    Route::get('/edit-power/{id}', [UserController::class, 'editPower'])->name('editPower');
    Route::post('/update-power/{id}', [UserController::class, 'updatePower'])->name('updatePower');

    //Error
    Route::get('/create-error', [UserController::class, 'createError'])->name('createError');
    Route::post('/save-error', [UserController::class, 'saveError'])->name('saveError');
    Route::get('/error-list', [UserController::class, 'ErrorList'])->name('ErrorList');
    Route::get('/edit-error/{id}', [UserController::class, 'editError'])->name('editError');
    Route::post('/update-error/{id}', [UserController::class, 'updateError'])->name('updateError');

    //Accessory
    Route::get('/create-accessory', [UserController::class, 'createAccessory'])->name('createAccessory');
    Route::post('/save-accessory', [UserController::class, 'saveAccessory'])->name('saveAccessory');
    Route::get('/accessory-list', [UserController::class, 'AccessoryList'])->name('AccessoryList');
    Route::get('/edit-accessory/{id}', [UserController::class, 'editAccessory'])->name('editAccessory');
    Route::post('/update-accessory/{id}', [UserController::class, 'updateAccessory'])->name('updateAccessory');

    //check item working
    Route::get('/search-machine', [IndexController::class, 'searchMachine'])->name('searchMachine');
    Route::post('/search-machine-result', [IndexController::class, 'searchMachineResult'])->name('searchMachineResult');
    Route::get('/add-new-machine-info/{serial_number}', [IndexController::class, 'addNewMachineInfo'])->name('addNewMachineInfo');
    Route::post('/save-new-machine-info', [IndexController::class, 'saveNewMachineInfo'])->name('saveNewMachineInfo');
    Route::get('/fetch-machine-details/{id}', [IndexController::class, 'fetchMachineDetails'])->name('fetchMachineDetails');
    Route::get('/add-machine-info/{id}', [IndexController::class, 'addMachineInfo'])->name('addMachineInfo');
    Route::post('/save-machine-info', [IndexController::class, 'saveMachineInfo'])->name('saveMachineInfo');
    Route::get('/exit-machine/{id}', [IndexController::class, 'exitMachine'])->name('exitMachine');
    Route::get('/mark-as-packed/{id}', [IndexController::class, 'markAsPacked'])->name('markAsPacked');
    Route::post('/save-exit-machine/{id}', [IndexController::class, 'saveExitMachine'])->name('saveExitMachine');
    Route::get('/edit-machine/{id}', [IndexController::class, 'editMachine'])->name('editMachine');
    Route::post('/save-edit-machine/{id}', [IndexController::class, 'saveEditMachine'])->name('saveEditMachine');
    Route::get('/print-slip/{id}', [IndexController::class, 'printThreeSlip'])->name('print-slip');

    //Reporting
    Route::get('/reporting', [IndexController::class, 'reporting'])->name('reporting');
    Route::get('/reporting-download', [IndexController::class, 'downloadMachineData'])->name('reporting-download');
    Route::get('/machine-list', [IndexController::class, 'machineList'])->name('machine-list');
    Route::post('/print-machine-slip', [IndexController::class, 'printSlip'])->name('print-machine-slip');
    Route::get('/print-single-machine-slip/{id}', [IndexController::class, 'singlePrintSlip'])->name('singlePrintSlip');
    Route::get('/barcode-print-single-machine-slip/{id}', [IndexController::class, 'singleBarcodePrintSlip'])->name('singleBarcodePrintSlip');
    Route::get('/pending-overall-complains', [IndexController::class, 'overallPending'])->name('pending-overall-complains');
    Route::get('/pending-monthly-complains', [IndexController::class, 'monthlyPending'])->name('pending-monthly-complains');
    Route::get('/resolved-overall-complains', [IndexController::class, 'overallComplete'])->name('resolved-overall-complains');
    Route::get('/resolved-monthly-complains', [IndexController::class, 'monthlyComplete'])->name('resolved-monthly-complains');
    Route::get('/custom-machine-report', [IndexController::class, 'customMachineData'])->name('custom-machine-report');
    Route::post('/custom-machine-report-pdf', [IndexController::class, 'customMachineDataPDF'])->name('custom-machine-report-pdf');

    //custom machine entry
    Route::get('create-custom-machine-data', [IndexController::class, 'createNewMachineInfo'])->name('create-custom-machine-data');
    Route::post('save-custom-machine-data', [IndexController::class, 'saveNewCreateMachineInfo'])->name('saveNewCreateMachineInfo');
    Route::get('custom-machine-list', [IndexController::class, 'customMachineList'])->name('custom-machine-list');

    //Warranty Check
    Route::post('warranty-check', [IndexController::class, 'warrantyCheck'])->name('warranty-check');
    // Logout
    Route::get('/user-logout', [IndexController::class, 'logout'])->name('userLogout');

    //inventory
    Route::get('/create-raw-material', [RawMaterialController::class, 'createRawMaterial'])->name('create-raw-material');
    Route::post('/save-raw-material', [RawMaterialController::class, 'saveRawMaterial'])->name('saveRawMaterial');
    Route::get('/raw-items-list', [RawMaterialController::class, 'rawMaterial'])->name('raw-items-list');
    Route::get('/view-inventory/{id}', [RawMaterialController::class, 'viewInventory'])->name('viewInventory');
    Route::get('/add-inventory/{id}', [RawMaterialController::class, 'addInventory'])->name('addInventory');
    Route::post('/save-inventory', [RawMaterialController::class, 'saveInventory'])->name('saveInventory');
    Route::get('/edit-material/{id}', [RawMaterialController::class, 'editMaterial'])->name('editMaterial');
    Route::post('/update-material', [RawMaterialController::class, 'updateRawMaterial'])->name('updateRawMaterial');

    Route::get('/semi-raw-items-list', [RawMaterialController::class, 'semiRawMaterial'])->name('semi-raw-items-list');
    Route::get('/create-semi-raw-material', [RawMaterialController::class, 'createSemiRawMaterial'])->name('create-semi-raw-material');
    Route::post('/save-semi-raw-material', [RawMaterialController::class, 'saveSemiRawMaterial'])->name('saveSemiRawMaterial');
    Route::get('/edit-semi-raw-material/{id}', [RawMaterialController::class, 'editSemiRawMaterial'])->name('editSemiRawMaterial');
    Route::post('/update-semi-raw-material', [RawMaterialController::class, 'updateSemiRawMaterial'])->name('updateSemiRawMaterial');

    Route::get('/finished-goods-list', [RawMaterialController::class, 'finishedGoodsList'])->name('finished-goods-list');
    Route::get('/create-finished-goods', [RawMaterialController::class, 'createFinishedGoods'])->name('create-finished-goods');
    Route::post('/save-finished-goods', [RawMaterialController::class, 'saveFinishedGoods'])->name('saveFinishedGoods');
    Route::get('/edit-finished-goods/{id}', [RawMaterialController::class, 'editFinishedGoods'])->name('editFinishedGoods');
    Route::post('/update-finished-goods', [RawMaterialController::class, 'updateFinishedGoods'])->name('updateFinishedGoods');

    Route::get('/unit-list', [UnitController::class, 'index'])->name('unit-list');
    Route::get('/create-new-unit', [UnitController::class, 'create'])->name('create-new-unit');
    Route::post('/save-new-unit', [UnitController::class, 'saveUnit'])->name('save-new-unit');

    //recipe making
    Route::get('/add-semi-raw-material-recipe/{id}', [RawMaterialController::class, 'addSemiRawMaterialRecipe'])->name('addSemiRawMaterialRecipe');
    Route::post('/save-semi-raw-material-recipe', [RawMaterialController::class, 'saveSemiRawMaterialRecipe'])->name('saveSemiRawMaterialRecipe');

    Route::get('/add-finished-goods-recipe/{id}', [RawMaterialController::class, 'addFinishedGoodsRecipe'])->name('addFinishedGoodsRecipe');
    Route::post('/save-finished-goods-recipe', [RawMaterialController::class, 'saveFinishedGoodsRecipe'])->name('saveFinishedGoodsRecipe');

    //production making
    Route::get('/add-semi-raw-material-production/{id}', [RawMaterialController::class, 'addSemiRawMaterialProduction'])->name('addSemiRawMaterialProduction');
    Route::post('/get-semi-raw-material-production', [RawMaterialController::class, 'getSemiRawMaterialProduction'])->name('get-semi-raw-material-production');
    Route::post('/save-semi-raw-material-production', [RawMaterialController::class, 'saveSemiRawMaterialProduction'])->name('saveSemiRawMaterialProduction');
    Route::get('/view-semi-raw-material-production/{id}', [RawMaterialController::class, 'viewSemiRawMaterialProduction'])->name('viewSemiRawMaterialProduction');

    Route::get('/add-finished-goods-production/{id}', [RawMaterialController::class, 'addFinishedGoodsProduction'])->name('addFinishedGoodsProduction');
    Route::post('/get-finished-goods-production', [RawMaterialController::class, 'getFinishedGoodsProduction'])->name('get-finished-goods-production');
    Route::post('/save-finished-goods-production', [RawMaterialController::class, 'saveFinishedGoodsProduction'])->name('saveFinishedGoodsProduction');
    Route::get('/view-finished-goods-production/{id}', [RawMaterialController::class, 'viewFinishedGoodsProduction'])->name('viewFinishedGoodsProduction');

    //sale module
    Route::get('/add-finished-goods-sales', [RawMaterialController::class, 'addFinishedGoodsSale'])->name('addFinishedGoodsSale');
    Route::post('/save-finished-goods-sales', [RawMaterialController::class, 'saveFinishedGoodsSale'])->name('saveFinishedGoodsSale');
    Route::get('/view-finished-goods-sales/{id}', [RawMaterialController::class, 'viewFinishedGoodsSale'])->name('viewFinishedGoodsSale');

    //transit inventory
    Route::get('/view-transit-inventory', [RawMaterialController::class, 'viewTransitInventory'])->name('viewTransitInventory');
    Route::get('/save-transit-inventory/{id}', [RawMaterialController::class, 'saveTransitInventory'])->name('saveTransitInventory');

    //serialization
    Route::get('create-serialization-data', [SerializationController::class, 'createSerialization'])->name('create-serialization-data');
    Route::post('save-serialization-data', [SerializationController::class, 'saveSerialization'])->name('saveSerialization');
    Route::get('serialization-list', [SerializationController::class, 'serializationList'])->name('serialization-list');
    Route::post('/add-serialization', [SerializationController::class, 'addSerialization'])->name('add-serialization');

    //new inventory
    Route::get('/create-ups-model', [ModelController::class, 'createUpsModel'])->name('create-ups-model');
    Route::post('/save-ups-model', [ModelController::class, 'saveUpsModel'])->name('saveUpsModel');
    Route::get('/ups-model-list', [ModelController::class, 'upsModel'])->name('ups-model-list');
    Route::get('/edit-ups-model/{id}', [ModelController::class, 'editUpsModel'])->name('editUpsModel');
    Route::post('/update-ups-model', [ModelController::class, 'updateUpsModel'])->name('updateUpsModel');

    Route::get('/add-ups-model-recipe/{id}', [ModelController::class, 'addUpsModelRecipe'])->name('addUpsModelRecipe');
    Route::post('/save-ups-model-recipe', [ModelController::class, 'saveUpsModelRecipe'])->name('saveUpsModelRecipe');

    Route::get('/create-finished-ups-model', [ModelController::class, 'createFinishedUpsModel'])->name('create-finished-ups-model');
    Route::post('/add-finished-ups-model', [ModelController::class, 'addFinishedUpsModel'])->name('add-finished-ups-model');
    Route::get('/finished-ups-model-list', [ModelController::class, 'finishedUpsModelList'])->name('finished-ups-model-list');
    Route::get('/in-complete-ups-model-list', [ModelController::class, 'inCompleteUpsModelList'])->name('in-complete-ups-model-list');
    Route::get('/select-model', [ModelController::class, 'selectModel'])->name('select-model');
    Route::post('/fetch-model-recipe', [ModelController::class, 'fetchModelRecipe'])->name('fetch-model-recipe');
    Route::post('/add-in-complete-ups-model', [ModelController::class, 'addInCompleteUpsModel'])->name('add-in-complete-ups-model');
    Route::get('/mark-complete-ups-model/{id}', [ModelController::class, 'markCompleteUpsModel'])->name('markCompleteUpsModel');
    Route::get('/fetch-in-complete-ups-model/{id}', [ModelController::class, 'fetchInCompleteModelRecipe'])->name('fetchInCompleteModelRecipe');
    Route::post('/saveRemainigInventory', [ModelController::class, 'saveRemainigInventory'])->name('saveRemainigInventory');

    //incomplete new working
    Route::post('/add-in-complete-ups-model-new', [ModelController::class, 'addInCompleteUpsModelNew'])->name('add-in-complete-ups-model-new');
    Route::get('/mark-complete-ups-model-new/{id}', [ModelController::class, 'markCompleteUpsModelNew'])->name('markCompleteUpsModelNew');
    Route::get('/fetch-in-complete-ups-model-new/{id}', [ModelController::class, 'fetchInCompleteModelRecipeNew'])->name('fetchInCompleteModelRecipeNew');
    Route::post('/saveRemainigInventoryNew', [ModelController::class, 'saveRemainigInventoryNew'])->name('saveRemainigInventoryNew');

    //check model production
    Route::get('/check-model-production/{id}', [ModelController::class, 'checkModelProduction'])->name('checkModelProduction');
    Route::post('/get-model-production', [ModelController::class, 'getModelProduction'])->name('get-model-production');

    //Machine Testing module
    Route::get('/view-testing-form', [MachineTestingController::class, 'viewTesting'])->name('view-testing-form');
    Route::post('/save-testing-form', [MachineTestingController::class, 'saveTesting'])->name('save-testing-form');
    Route::get('/view-testing-list', [MachineTestingController::class, 'viewTestingList'])->name('view-testing-list');
    Route::get('/assign-unit', [MachineTestingController::class, 'assignUnit'])->name('assign-unit');
    Route::post('/save-assign-unit', [MachineTestingController::class, 'saveAssignUnit'])->name('save-assign-unit');

    //quickbook
    Route::post('product-services', [QuickBookController::class, 'productService'])->name('productServiceWarehouse');
    Route::get('product-services', [QuickBookController::class, 'productService'])->name('productService');
    Route::get('product-categories', [QuickBookController::class, 'productCategories'])->name('productCategories');
    Route::get('low-stock-products', [QuickBookController::class, 'lowStockProducts'])->name('lowStockProducts');
    Route::get('out-stock-products', [QuickBookController::class, 'outStockProducts'])->name('outStockProducts');
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        $controller = \App\Http\Controllers\Warehouse\WarehouseController::class;
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/production-availability', [$controller, 'productionAvailability'])->name('productionAvailability');
        Route::get('/availability', [$controller, 'availability'])->name('availability');
        Route::get('/context', [$controller, 'context'])->name('context');
        Route::get('/unassigned', [$controller, 'unassigned'])->name('unassigned');
        Route::post('/unassigned/assign', [$controller, 'assignUnassigned'])->name('assignUnassigned');
        Route::post('/save', [$controller, 'saveWarehouse'])->name('save');
        Route::post('/delete', [$controller, 'deleteWarehouse'])->name('delete');
        Route::post('/locations', [$controller, 'saveLocation'])->name('locations');
        Route::post('/reorder', [$controller, 'reorder'])->name('reorder');
        Route::post('/movement', [$controller, 'post'])->name('post');
        Route::post('/transit-receipt', [$controller, 'receipt'])->name('receipt');
        Route::post('/transfer', [$controller, 'transfer'])->name('transfer');
        Route::post('/quick-transfer', [$controller, 'quickTransfer'])->name('quickTransfer');
        Route::get('/transfers', [$controller, 'transfers'])->name('transfers');
        Route::post('/transfer/process', [$controller, 'processTransfer'])->name('process');
        Route::get('/history', [$controller, 'history'])->name('history');
    });
    Route::get('in-transit-inventory', [QuickBookController::class, 'inTransitInventory'])->name('inTransitInventory');
    Route::post('save-transit-inventory-new', [QuickBookController::class, 'saveTransitInventoryNew'])->name('saveTransitInventoryNew');
    Route::get('received-transit-inventory', [QuickBookController::class, 'receivedTransitInventory'])->name('receivedTransitInventory');
    Route::get('/mark-received-transit/{id}', [QuickBookController::class, 'markReceivedTransit'])->name('markReceivedTransit');
    Route::post('/mark-received-transit-new', [QuickBookController::class, 'markReceivedTransitNew'])->name('markReceivedTransitNew');
    Route::post('save-product-category', [QuickBookController::class, 'saveProductCategory'])->name('saveProductCategory');
    Route::get('get-category', [QuickBookController::class, 'getCategory'])->name('getCategory');
    Route::post('save-product-asset', [QuickBookController::class, 'saveProductAsset'])->name('saveProductAsset');
    Route::get('get-asset', [QuickBookController::class, 'getAsset'])->name('getAsset');
    Route::post('save-income-account', [QuickBookController::class, 'saveIncomeAccount'])->name('saveIncomeAccount');
    Route::get('get-income-account', [QuickBookController::class, 'getIncomeAccount'])->name('getIncomeAccount');
    Route::post('save-expense-account', [QuickBookController::class, 'saveExpenseAccount'])->name('saveExpenseAccount');
    Route::get('get-expense-account', [QuickBookController::class, 'getExpenseAccount'])->name('getExpenseAccount');
    Route::get('get-product-service', [QuickBookController::class, 'getProductService'])->name('getProductService');
    Route::get('get-customer', [QuickBookController::class, 'getCustomer'])->name('getCustomer');
    Route::post('save-customer', [QuickBookController::class, 'saveCustomer'])->name('saveCustomer');
    Route::get('product-estimates', [QuickBookController::class, 'productEstimates'])->name('productEstimates');
    Route::get('create-product-estimates', [QuickBookController::class, 'createProductEstimates'])->name('createProductEstimates');
    Route::post('production-estimates/preview', [\App\Http\Controllers\user_portal\ProductionEstimationController::class, 'preview'])->name('productionEstimation.preview');
    Route::post('production-estimates', [\App\Http\Controllers\user_portal\ProductionEstimationController::class, 'store'])->name('productionEstimation.store');
    Route::get('production-estimates/{id}', [\App\Http\Controllers\user_portal\ProductionEstimationController::class, 'show'])->name('productionEstimation.show');
    Route::get('production-estimates/{id}/pdf', [\App\Http\Controllers\user_portal\ProductionEstimationController::class, 'pdf'])->name('productionEstimation.pdf');
    Route::get('production-estimates/{id}/csv', [\App\Http\Controllers\user_portal\ProductionEstimationController::class, 'csv'])->name('productionEstimation.csv');
    Route::post('production-estimates/{id}/archive', [\App\Http\Controllers\user_portal\ProductionEstimationController::class, 'archive'])->name('productionEstimation.archive');
    Route::post('get-product-estimates', [QuickBookController::class, 'getProductEstimates'])->name('getProductEstimates');
    Route::post('save-product-estimates', [QuickBookController::class, 'saveProductEstimates'])->name('saveProductEstimates');

    Route::get('/print-single-estimate/{id}', [QuickBookController::class, 'SingleEstimateDownload'])->name('SingleEstimateDownload');
    Route::get('product-estimates-calculation', [QuickBookController::class, 'productEstimatesCalculation'])->name('productEstimatesCalculation');
    Route::get('download-product-estimates-calculation', [QuickBookController::class, 'downloadProductEstimatesCalculation'])->name('downloadProductEstimatesCalculation');
    Route::get('/delete-estimate/{id}', [QuickBookController::class, 'deleteEstimate'])->name('deleteEstimate');
    Route::get('get-invoice', [QuickBookController::class, 'getInvoice'])->name('getInvoice');
    Route::post('save-invoice', [QuickBookController::class, 'saveInvoice'])->name('saveInvoice');
    Route::get('/print-single-invoice/{id}', [QuickBookController::class, 'SingleInvoiceDownload'])->name('SingleInvoiceDownload');
    Route::get('get-invoice-product-service', [QuickBookController::class, 'getInvoiceProductService'])->name('getInvoiceProductService');
    Route::get('get-product-quantity/{id}', [QuickBookController::class, 'getProductQuantity'])->name('getProductQuantity');
    Route::get('/inventory-ledger/{id}', [QuickBookController::class, 'inventoryReport'])->name('inventoryReport');
    Route::post('update-product-info', [QuickBookController::class, 'updateProductInfo'])->name('updateProductInfo');
    Route::get('/delete-product/{id}', [QuickBookController::class, 'deleteProduct'])->name('deleteProduct');
    Route::get('/active-product/{id}', [QuickBookController::class, 'activeProduct'])->name('activeProduct');
    Route::get('/delete-category/{id}', [QuickBookController::class, 'deleteCategory'])->name('deleteCategory');
    Route::get('/active-category/{id}', [QuickBookController::class, 'activeCategory'])->name('activeCategory');

    Route::get('/edit-bundle-products/{id}', [QuickBookController::class, 'editBundleProducts'])->name('editBundleProducts');
    Route::post('/update-bundle-products/{id}', [QuickBookController::class, 'updateBundleProducts'])->name('updateBundleProducts');

    Route::get('/product-test', function () {
        return view('user_portal.New.customers');
    })->name('product-test');
});





/**** Theme Routes *****/
// Auth::routes(['verify' => true]);

// dashboard Routes
Route::get('/ecommerce-dashboard', [DashboardController::class, 'dashboardEcommerce'])->name('ecommerce-dashboard');

Route::group(['prefix' => 'dashboard'], function () {
    Route::get('ecommerce', [DashboardController::class, 'dashboardEcommerce'])->name('dashboard-ecommerce');
    Route::get('analytics', [DashboardController::class, 'dashboardAnalytics'])->name('dashboard-analytics');
});

//Application Routes
Route::group(['prefix' => 'app'], function () {
    Route::get('email', [ApplicationController::class, 'emailApplication'])->name('app-email');
    Route::get('chat', [ApplicationController::class, 'chatApplication'])->name('app-chat');
    Route::get('todo', [ApplicationController::class, 'todoApplication'])->name('app-todo');
    Route::get('calendar', [ApplicationController::class, 'calendarApplication'])->name('app-calendar');
    Route::get('kanban', [ApplicationController::class, 'kanbanApplication'])->name('app-kanban');
    Route::get('invoice/view', [ApplicationController::class, 'invoiceApplication'])->name('app-invoice-view');
    Route::get('invoice/list', [ApplicationController::class, 'invoiceListApplication'])->name('app-invoice-list');
    Route::get('invoice/edit', [ApplicationController::class, 'invoiceEditApplication'])->name('app-invoice-edit');
    Route::get('invoice/add', [ApplicationController::class, 'invoiceAddApplication'])->name('app-invoice-add');
    Route::get('file-manager', [ApplicationController::class, 'fileManagerApplication'])->name('app-file-manager');
    // User Route
    Route::get('users/list', [UsersController::class, 'listUser'])->name('app-users-list');
    Route::get('users/view', [UsersController::class, 'viewUser'])->name('app-users-view');
    Route::get('users/edit', [UsersController::class, 'editUser'])->name('app-users-edit');
});

// Content Page Routes
Route::group(['prefix' => 'content'], function () {
    Route::get('grid', [ContentController::class, 'gridContent'])->name('content-grid');
    Route::get('typography', [ContentController::class, 'typographyContent'])->name('content-typography');
    Route::get('text-utilities', [ContentController::class, 'textUtilitiesContent'])->name('content-text-utilities');
    Route::get('syntax-highlighter', [ContentController::class, 'contentSyntaxHighlighter'])->name('content-syntax-highlighter');
    Route::get('helper-classes', [ContentController::class, 'contentHelperClasses'])->name('content-helper-classes');
    Route::get('colors', [ContentController::class, 'colorContent'])->name('content-colors');
});

// icons
Route::group(['prefix' => 'icons'], function () {
    Route::get('livicons', [IconsController::class, 'liveIcons'])->name('icons-livicons');
    Route::get('boxicons', [IconsController::class, 'boxIcons'])->name('icons-boxicons');
});

// card
Route::group(['prefix' => 'card'], function () {
    Route::get('basic', [CardController::class, 'basicCard'])->name('card-basic');
    Route::get('actions', [CardController::class, 'actionCard'])->name('card-actions');
    Route::get('widgets', [CardController::class, 'widgets'])->name('card-widgets');
});


// component route
Route::group(['prefix' => 'component'], function () {
    Route::get('alerts', [ComponentController::class, 'alertComponenet'])->name('component-alerts');
    Route::get('buttons-basic', [ComponentController::class, 'buttonComponenet'])->name('component-buttons-basic');
    Route::get('breadcrumbs', [ComponentController::class, 'breadcrumbsComponenet'])->name('component-breadcrumbs');
    Route::get('carousel', [ComponentController::class, 'carouselComponenet'])->name('component-carousel');
    Route::get('collapse', [ComponentController::class, 'collapseComponenet'])->name('component-collapse');
    Route::get('dropdowns', [ComponentController::class, 'dropdownComponenet'])->name('component-dropdowns');
    Route::get('list-group', [ComponentController::class, 'listGroupComponenet'])->name('component-list-group');
    Route::get('modals', [ComponentController::class, 'modalComponenet'])->name('component-modals');
    Route::get('pagination', [ComponentController::class, 'paginationComponenet'])->name('component-pagination');
    Route::get('navbar', [ComponentController::class, 'navbarComponenet'])->name('component-navbar');
    Route::get('tabs-component', [ComponentController::class, 'tabsComponenet'])->name('component-tabs-component');
    Route::get('pills-component', [ComponentController::class, 'pillComponenet'])->name('component-pills-component');
    Route::get('tooltips', [ComponentController::class, 'tooltipsComponenet'])->name('component-tooltips');
    Route::get('popovers', [ComponentController::class, 'popoversComponenet'])->name('component-popovers');
    Route::get('badges', [ComponentController::class, 'badgesComponenet'])->name('component-badges');
    Route::get('pill-badges', [ComponentController::class, 'pillBadgesComponenet'])->name('component-pill-badges');
    Route::get('progress', [ComponentController::class, 'progressComponenet'])->name('component-progress');
    Route::get('media-objects', [ComponentController::class, 'mediaObjectComponenet'])->name('component-media-objects');
    Route::get('spinner', [ComponentController::class, 'spinnerComponenet'])->name('component-spinner');
    Route::get('bs-toast', [ComponentController::class, 'toastsComponenet'])->name('component-bs-toast');
});

// extra component
Route::group(['prefix' => 'extra-component'], function () {
    Route::get('avatar', [ExComponentController::class, 'avatarComponent'])->name('extra-component-avatar');
    Route::get('chips', [ExComponentController::class, 'chipsComponent'])->name('extra-component-chips');
    Route::get('divider', [ExComponentController::class, 'dividerComponent'])->name('extra-component-divider');
});

// form elements
Route::group(['prefix' => 'form'], function () {
    Route::get('inputs', [FormController::class, 'inputForm'])->name('form-inputs');
    Route::get('input-groups', [FormController::class, 'inputGroupForm'])->name('form-input-groups');
    Route::get('number-input', [FormController::class, 'numberInputForm'])->name('form-number-input');
    Route::get('select', [FormController::class, 'selectForm'])->name('form-select');
    Route::get('radio', [FormController::class, 'radioForm'])->name('form-radio');
    Route::get('checkbox', [FormController::class, 'checkboxForm'])->name('form-checkbox');
    Route::get('switch', [FormController::class, 'switchForm'])->name('form-switch');
    Route::get('textarea', [FormController::class, 'textareaForm'])->name('form-textarea');
    Route::get('quill-editor', [FormController::class, 'quillEditorForm'])->name('form-quill-editor');
    Route::get('file-uploader', [FormController::class, 'fileUploaderForm'])->name('form-file-uploader');
    Route::get('date-time-picker', [FormController::class, 'datePickerForm'])->name('form-date-time-picker');
    Route::get('layout', [FormController::class, 'formLayout'])->name('form-layout');
    Route::get('wizard', [FormController::class, 'formWizard'])->name('form-wizard');
    Route::get('validation', [FormController::class, 'formValidation'])->name('form-validation');
    Route::get('repeater', [FormController::class, 'formRepeater'])->name('form-repeater');
});

// table route
Route::group(['prefix' => 'table'], function () {
    Route::get('', [TableController::class, 'basicTable'])->name('table');
    Route::get('extended', [TableController::class, 'extendedTable'])->name('table-extended');
    Route::get('datatable', [TableController::class, 'dataTable'])->name('table-datatable');
});

// page Route
Route::group(['prefix' => 'page'], function () {
    Route::get('user/profile', [PageController::class, 'userProfilePage'])->name('page-user-profile');
    Route::get('faq', [PageController::class, 'faqPage'])->name('page-faq');
    Route::get('knowledge-base', [PageController::class, 'knowledgeBasePage'])->name('page-knowledge-base');
    Route::get('knowledge-base/categories', [PageController::class, 'knowledgeCatPage'])->name('page-knowledge-base-categories');
    Route::get('knowledge-base/categories/question', [PageController::class, 'knowledgeQuestionPage'])->name('page-knowledge-base-question');
    Route::get('search', [PageController::class, 'searchPage'])->name('page-search');
    Route::get('account-settings', [PageController::class, 'accountSettingPage'])->name('page-account-settings');
});

// Authentication  Route
Route::group(['prefix' => 'auth'], function () {
    Route::get('login1', [AuthenticationController::class, 'loginPage'])->name('auth-login');
    Route::get('register', [AuthenticationController::class, 'registerPage'])->name('auth-register');
    Route::get('forgot-password', [AuthenticationController::class, 'forgetPasswordPage'])->name('auth-forgot-password');
    Route::get('reset-password', [AuthenticationController::class, 'resetPasswordPage'])->name('auth-reset-password');
    Route::get('lock-screen', [AuthenticationController::class, 'authLockPage'])->name('auth-lock-screen');
});

// Miscellaneous
Route::group(['prefix' => 'misc'], function () {
    Route::get('coming-soon', [MiscellaneousController::class, 'comingSoonPage'])->name('misc-coming-soon');
    Route::get('error-404', [MiscellaneousController::class, 'error404Page'])->name('misc-error-404');
    Route::get('error-500', [MiscellaneousController::class, 'error500Page'])->name('misc-error-500');
    Route::get('not-authorized', [MiscellaneousController::class, 'notAuthPage'])->name('misc-not-authorized');
    Route::get('maintenance', [MiscellaneousController::class, 'maintenancePage'])->name('misc-maintenance');
});

// Charts Route
Route::group(['prefix' => 'chart'], function () {
    Route::get('apex', [ChartController::class, 'apexChart'])->name('chart-apex');
    Route::get('chartjs', [ChartController::class, 'chartJs'])->name('chart-chartjs');
    Route::get('chartist', [ChartController::class, 'chartist'])->name('chart-chartist');
});

Route::get('maps/leaflet', [ChartController::class, 'leafletMap'])->name('maps-leaflet');

// extension route
Route::group(['prefix' => 'extension'], function () {
    Route::get('sweet-alerts', [ExtensionsController::class, 'sweetAlert'])->name('extension-sweet-alerts');
    Route::get('toastr', [ExtensionsController::class, 'toastr'])->name('extension-toastr');
    Route::get('noui-slider', [ExtensionsController::class, 'noUiSlider'])->name('extension-noui-slider');
    Route::get('drag-drop', [ExtensionsController::class, 'dragComponent'])->name('extension-drag-drop');
    Route::get('tour', [ExtensionsController::class, 'tourComponent'])->name('extension-tour');
    Route::get('swiper', [ExtensionsController::class, 'swiperComponent'])->name('extension-swiper');
    Route::get('treeview', [ExtensionsController::class, 'treeviewComponent'])->name('extension-treeview');
    Route::get('block-ui', [ExtensionsController::class, 'blockUIComponent'])->name('extension-block-ui');
    Route::get('media-player', [ExtensionsController::class, 'mediaComponent'])->name('extension-media-player');
    Route::get('miscellaneous', [ExtensionsController::class, 'miscellaneous'])->name('extension-miscellaneous');
    Route::get('locale', [ExtensionsController::class, 'locale'])->name('extension-locale');
    Route::get('ratings', [ExtensionsController::class, 'ratings'])->name('extension-ratings');
});

// locale Route
Route::get('lang/{locale}', [LanguageController::class, 'swap'])->name('lang-locale');












