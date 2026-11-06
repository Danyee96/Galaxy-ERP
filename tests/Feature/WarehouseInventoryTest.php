<?php
namespace Tests\Feature;
use App\Models\{Products,RawMaterial,SemiRawMaterial,FinishedGoods,FinishedModels,User,ProductInventory};
use App\Models\Warehouse\{Warehouse,Location,Item,Balance,Movement,Transfer};
use App\Services\Warehouse\{Inventory,Quantity};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
class WarehouseInventoryTest extends TestCase
{
    private $path; private $source; private $item; private $loc; private $second; private $inventory;
    protected function setUp(): void
    {
        parent::setUp(); $this->path=tempnam(sys_get_temp_dir(),'galaxy-warehouse-test-');
        config(['warehouses.enabled'=>false,'database.default'=>'warehouse_test','database.connections.warehouse_test'=>['driver'=>'sqlite','database'=>$this->path,'prefix'=>'','foreign_key_constraints'=>true]]);
        DB::purge('warehouse_test'); DB::statement('PRAGMA busy_timeout = 5000');
        $this->assertSame($this->path,DB::connection()->getDatabaseName());
        foreach(['products','raw_materials','semi_raw_materials','finished_goods'] as $table)Schema::create($table,function(Blueprint $t)use($table){$t->id();$t->string('name');$t->string('sku')->nullable();$t->string('type')->default('Inventory');$t->integer('status')->default(1);foreach(['total_qty','temp_qty','current_stock','temp_stock','air_transit','sea_transit','transit_stock'] as $c)$t->string($c)->nullable();$t->timestamps();});
        Schema::create('finished_models',function(Blueprint $t){$t->id();$t->string('serial_number');$t->integer('model_id')->nullable();$t->integer('is_complete')->default(1);$t->integer('status')->default(0);$t->timestamps();});
        Schema::create('product_inventories',function(Blueprint $t){$t->id();foreach(['product_id','transit_id','status','received_by','remaining_stock','received_stock']as$c)$t->integer($c)->nullable();$t->string('stock_in');$t->timestamps();});
        Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');});
        require_once database_path('migrations/2026_11_06_000000_create_warehouse_inventory.php'); (new \CreateWarehouseInventory)->up();
        $this->source=Products::create(['name'=>'Battery','sku'=>'BAT','type'=>'Inventory','total_qty'=>'40','temp_qty'=>'40','air_transit'=>'10','sea_transit'=>'0']);
        RawMaterial::create(['name'=>'Copper','current_stock'=>'20','temp_stock'=>'20','transit_stock'=>'0']);
        SemiRawMaterial::create(['name'=>'Coil','current_stock'=>'3','temp_stock'=>'3']);
        FinishedGoods::create(['name'=>'Panel','current_stock'=>'2','temp_stock'=>'2']);
        FinishedModels::create(['serial_number'=>'UPS-1']);
        $this->artisan('warehouses:backfill',['--apply'=>true])->assertExitCode(0);
        config(['warehouses.enabled'=>true]);
        $this->actingAs((new User)->forceFill(['id'=>99,'name'=>'Warehouse tester','role_id'=>1]));
        $this->inventory=app(Inventory::class);$this->item=Item::where('source_type','product')->firstOrFail();
        $this->loc=Location::findOrFail(DB::table('warehouse_state')->where('key','default_location')->value('value'));
        $warehouse=Warehouse::create(['code'=>'SECOND','name'=>'Second warehouse']);
        $this->second=Location::create(['warehouse_id'=>$warehouse->id,'rack'=>'R2','row'=>'3','column'=>'5']);
    }
    protected function tearDown(): void { config(['warehouses.enabled'=>false]);DB::disconnect('warehouse_test');if($this->path&&file_exists($this->path))unlink($this->path);parent::tearDown(); }
    private function input(array $extra=[]): array {return array_merge(['request_key'=>(string)Str::uuid(),'date'=>'2026-11-06','reference'=>'TEST','item_id'=>$this->item->id,'warehouse_id'=>$this->second->warehouse_id,'location_id'=>$this->second->id,'quantity'=>'5.2500'],$extra);}
    public function test_backfill_preserves_all_stock_sources_and_is_idempotent():void
    {
        $this->assertSame(5,Item::count());$this->assertSame(5,Movement::count());
        $this->assertSame('40',$this->source->fresh()->total_qty);
        $this->assertSame(400000,Quantity::units($this->item->balances()->sum('quantity')));
        $this->artisan('warehouses:backfill',['--apply'=>true])->assertExitCode(0);
        $this->assertSame(5,Movement::count());$this->assertSame(5,Item::count());
    }
    public function test_inbound_outbound_decimals_multiple_locations_and_reconciliation():void
    {
        $this->inventory->post('inbound',$this->input());
        $this->assertSame('45.2500',$this->source->fresh()->total_qty);
        $this->inventory->post('outbound',$this->input(['quantity'=>'1.1250']));
        $this->assertSame('44.1250',$this->source->fresh()->total_qty);
        $this->assertSame(400000,Quantity::units(Balance::where('item_id',$this->item->id)->where('location_id',$this->loc->id)->value('quantity')));
        $this->assertSame(41250,Quantity::units(Balance::where('item_id',$this->item->id)->where('location_id',$this->second->id)->value('quantity')));
        $this->assertSame(Quantity::units($this->source->fresh()->total_qty),Quantity::units($this->item->balances()->sum('quantity')));
    }
    public function test_retry_key_prevents_duplicate_movements_and_rejects_changed_input():void
    {
        $input=$this->input();$a=$this->inventory->post('inbound',$input);$b=$this->inventory->post('inbound',$input);$this->assertSame($a->id,$b->id);
        $this->expectException(ValidationException::class);$this->inventory->post('inbound',array_merge($input,['quantity'=>'6']));
    }
    public function test_insufficient_stock_rolls_back_aggregate_and_ledger():void
    {
        try{$this->inventory->post('outbound',$this->input(['quantity'=>'100']));$this->fail('Expected insufficient stock.');}catch(ValidationException $e){}
        $this->assertSame('40',$this->source->fresh()->total_qty);$this->assertSame(5,Movement::count());
    }
    public function test_partial_internal_transfer_keeps_global_stock_constant_and_rejects_replay():void
    {
        $op=$this->inventory->transfer(['request_key'=>(string)Str::uuid(),'reference'=>'MOVE','date'=>'2026-11-06','lines'=>[['item_id'=>$this->item->id,'source_location_id'=>$this->loc->id,'destination_location_id'=>$this->second->id,'quantity'=>'12']]]);
        $id=$op->result['transfer_id'];$dispatch=['request_key'=>(string)Str::uuid(),'transfer_id'=>$id];$this->inventory->dispatch($dispatch);$this->inventory->dispatch($dispatch);
        $line=Transfer::findOrFail($id)->lines()->first();
        $this->inventory->receiveTransfer(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id,'lines'=>[['id'=>$line->id,'quantity'=>'5']]]);
        $this->assertSame('partially_received',Transfer::find($id)->status);
        $this->inventory->receiveTransfer(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id,'lines'=>[['id'=>$line->id,'quantity'=>'7']]]);
        $this->assertSame('completed',Transfer::find($id)->status);$this->assertSame('40',$this->source->fresh()->total_qty);
        $this->assertSame(400000,Quantity::units($this->item->balances()->sum('quantity')));
        $this->expectException(ValidationException::class);$this->inventory->receiveTransfer(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id,'lines'=>[['id'=>$line->id,'quantity'=>'1']]]);
    }
    public function test_partial_transit_receipt_updates_only_destination_and_does_not_add_projected_stock_twice():void
    {
        $doc=ProductInventory::create(['product_id'=>$this->source->id,'transit_id'=>2,'status'=>0,'stock_in'=>'10','received_stock'=>0,'remaining_stock'=>10]);
        $input=['request_key'=>(string)Str::uuid(),'document_type'=>'product','document_id'=>$doc->id,'location_id'=>$this->second->id,'quantity'=>'4'];
        $this->inventory->receiveTransit($input);$this->inventory->receiveTransit($input);
        $this->assertSame(6,$doc->fresh()->remaining_stock);$this->assertSame('44.0000',$this->source->fresh()->total_qty);$this->assertSame('40',$this->source->fresh()->temp_qty);
        $this->inventory->receiveTransit(array_merge($input,['request_key'=>(string)Str::uuid(),'quantity'=>'6']));
        $this->assertSame(1,$doc->fresh()->status);$this->assertSame('50.0000',$this->source->fresh()->total_qty);$this->assertSame('0.0000',$this->source->fresh()->air_transit);
    }
    public function test_legacy_production_deltas_are_atomic_and_warehouse_aware():void
    {
        $raw=RawMaterial::first();
        $this->inventory->atomic(function()use($raw){$raw->update(['current_stock'=>'15','temp_stock'=>'15']);$this->source->update(['total_qty'=>'42','temp_qty'=>'42']);});
        $this->assertSame(150000,Quantity::units(Item::where('source_type','raw')->first()->balances()->sum('quantity')));
        $this->assertSame(420000,Quantity::units($this->item->balances()->sum('quantity')));
        try{$this->inventory->atomic(function()use($raw){$raw->refresh()->update(['current_stock'=>'14']);$this->source->refresh()->update(['total_qty'=>'-10']);});$this->fail('Must roll back');}catch(ValidationException $e){}
        $this->assertSame('15',$raw->fresh()->current_stock);$this->assertSame('42',$this->source->fresh()->total_qty);
    }
    public function test_warehouse_rename_locations_cross_ownership_and_permission_checks():void
    {
        $w=$this->second->warehouse;$this->post(route('warehouses.save'),['id'=>$w->id,'code'=>'SECOND','name'=>'Renamed','active'=>1])->assertRedirect();
        $this->assertStringContainsString('Renamed',$this->second->fresh()->label);
        $this->postJson(route('warehouses.post'),$this->input(['type'=>'inbound','warehouse_id'=>$this->loc->warehouse_id]))->assertStatus(422);
        $this->get(route('warehouses.index'))->assertOk()->assertSee('Renamed');
        $this->actingAs((new User)->forceFill(['id'=>98,'name'=>'Other','role_id'=>2]));$this->get(route('warehouses.index'))->assertForbidden();
    }
    public function test_locations_rename_and_warehouse_history_prevent_deletion():void
    {
        $this->post(route('warehouses.locations'),['warehouse_id'=>$this->second->warehouse_id,'rack'=>'R3','row'=>'4','column'=>'6','active'=>1,'reorder_level'=>'2.5'])->assertRedirect();
        $this->assertSame(2,Location::where('warehouse_id',$this->second->warehouse_id)->count());
        $this->inventory->post('inbound',$this->input());
        $this->postJson(route('warehouses.delete'),['id'=>$this->second->warehouse_id])->assertStatus(422);
        $this->postJson(route('warehouses.locations'),['id'=>$this->second->id,'warehouse_id'=>$this->loc->warehouse_id,'row'=>'4','column'=>'6','active'=>1,'reorder_level'=>0])->assertStatus(422);
        $this->get(route('warehouses.history'))->assertOk()->assertSee('Battery')->assertSee('Second warehouse');
        $csv=$this->get(route('warehouses.history',['csv'=>1]))->assertOk()->streamedContent();
        $this->assertStringContainsString('R2 / 3 / 5',$csv);
    }
    public function test_cancel_partial_transfer_returns_only_unreceived_stock():void
    {
        $op=$this->inventory->transfer(['request_key'=>(string)Str::uuid(),'reference'=>'CANCEL','lines'=>[['item_id'=>$this->item->id,'source_location_id'=>$this->loc->id,'destination_location_id'=>$this->second->id,'quantity'=>'10']]]);
        $id=$op->result['transfer_id'];$this->inventory->dispatch(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id]);$line=Transfer::find($id)->lines()->first();
        $this->inventory->receiveTransfer(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id,'lines'=>[['id'=>$line->id,'quantity'=>'3']]]);
        $this->inventory->cancelTransfer(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id]);
        $this->assertSame('cancelled',Transfer::find($id)->status);
        $this->assertSame(370000,Quantity::units(Balance::where('item_id',$this->item->id)->where('location_id',$this->loc->id)->value('quantity')));
        $this->assertSame(30000,Quantity::units(Balance::where('item_id',$this->item->id)->where('location_id',$this->second->id)->value('quantity')));
        $this->assertSame('40',$this->source->fresh()->total_qty);
    }
    public function test_multi_system_estimation_uses_selected_warehouses_without_stock_changes():void
    {
        Schema::create('bundles',function(Blueprint $t){$t->id();$t->integer('bundle_id');$t->integer('product_id');$t->decimal('quantity',20,4);$t->integer('status')->default(1);$t->timestamps();});
        Schema::create('product_estimates',function(Blueprint $t){$t->id();$t->string('title')->nullable();$t->string('number')->nullable();$t->integer('bundle_id')->nullable();$t->integer('quantity')->nullable();$t->integer('status')->default(1);$t->timestamps();});
        Schema::create('estimate_values',function(Blueprint $t){$t->id();$t->integer('estimate_id');$t->integer('product_id');$t->integer('quantity_in_stock');});
        require_once database_path('migrations/2026_11_05_000000_add_multi_system_estimation_planning.php');(new \AddMultiSystemEstimationPlanning)->up();
        $model=$this->inventory->atomic(fn()=>Products::create(['name'=>'System A','type'=>'Bundle','total_qty'=>'0','temp_qty'=>'0','status'=>1]));
        $modelB=$this->inventory->atomic(fn()=>Products::create(['name'=>'System B','type'=>'Bundle','total_qty'=>'0','temp_qty'=>'0','status'=>1]));
        \App\Models\Bundle::create(['bundle_id'=>$model->id,'product_id'=>$this->source->id,'quantity'=>2,'status'=>1]);
        \App\Models\Bundle::create(['bundle_id'=>$modelB->id,'product_id'=>$this->source->id,'quantity'=>4,'status'=>1]);
        $this->inventory->post('inbound',$this->input(['quantity'=>'10']));
        $before=Movement::count();$planner=app(\App\Services\Estimation\ProductionEstimationPlanner::class);
        $input=['title'=>'Warehouse plan','plan_date'=>'2026-11-06','mode'=>'manual','models'=>[['product_id'=>$model->id,'quantity'=>4,'weight'=>1],['product_id'=>$modelB->id,'quantity'=>2,'weight'=>1]],'warehouse_ids'=>[$this->second->warehouse_id],'request_key'=>(string)Str::uuid()];
        $plan=$planner->preview($input);$this->assertSame('10',$plan['components'][0]['physical_stock']);$this->assertSame('6',$plan['components'][0]['shortage']);
        $this->assertSame($this->second->warehouse_id,$plan['components'][0]['warehouses'][0]['warehouse_id']);
        $input['preview_fingerprint']=$plan['fingerprint'];$saved=$planner->save($input,99);
        $this->assertSame(1,count($saved->calculation_snapshot['components'][0]['warehouses']));
        $this->second->warehouse->update(['name'=>'Renamed planning warehouse']);
        $this->assertSame('Renamed planning warehouse',$saved->planningSnapshot()['components'][0]['warehouses'][0]['name']);
        $this->assertSame('Second warehouse',$saved->fresh()->calculation_snapshot['components'][0]['warehouses'][0]['name']);
        $input['warehouse_ids']=[$this->loc->warehouse_id,$this->second->warehouse_id];$multiple=$planner->preview($input);$this->assertSame('50',$multiple['components'][0]['physical_stock']);
        $input['warehouse_ids']=[];$combined=$planner->preview($input);$this->assertSame('50',$combined['components'][0]['physical_stock']);
        $this->assertSame($before,Movement::count());$this->assertSame('50.0000',$this->source->fresh()->total_qty);
    }
    public function test_concurrent_outbound_transactions_cannot_oversell():void
    {
        if(!function_exists('pcntl_fork'))$this->markTestSkipped('Requires pcntl.');
        $input=$this->input(['quantity'=>'30','warehouse_id'=>$this->loc->warehouse_id,'location_id'=>$this->loc->id]);
        DB::disconnect('warehouse_test');$pids=[];$files=[];
        for($i=0;$i<2;$i++){
            $file=tempnam(sys_get_temp_dir(),'warehouse-concurrent-');$files[]=$file;$pid=pcntl_fork();
            if($pid===0){try{DB::reconnect('warehouse_test');DB::statement('PRAGMA busy_timeout = 5000');$input['request_key']=(string)Str::uuid();app(Inventory::class)->post('outbound',$input);file_put_contents($file,'posted');}catch(ValidationException $e){file_put_contents($file,'insufficient');}catch(\Throwable $e){file_put_contents($file,get_class($e).':'.$e->getMessage());}exit(0);} $pids[]=$pid;
        }
        foreach($pids as $pid)pcntl_waitpid($pid,$status);DB::reconnect('warehouse_test');
        $results=array_map('file_get_contents',$files);foreach($files as $file)unlink($file);sort($results);
        $this->assertSame(['insufficient','posted'],$results);$this->assertSame('10.0000',$this->source->fresh()->total_qty);
        $this->assertSame(100000,Quantity::units($this->item->balances()->sum('quantity')));
    }

    public function test_legacy_invoice_issues_selected_stock_once_and_rolls_back_insufficient_sales():void
    {
        Schema::create('invoices',function(Blueprint $t){$t->id();$t->string('invoice_number');$t->string('invoice_name');$t->integer('total_count');$t->integer('created_by');$t->timestamps();});
        Schema::create('invoice_details',function(Blueprint $t){$t->id();$t->integer('invoice_id');$t->integer('product_id');$t->integer('product_qty');$t->timestamps();});
        Schema::table('product_inventories',function(Blueprint $t){$t->string('description')->nullable();$t->integer('entry_by')->nullable();});
        $this->inventory->post('inbound',$this->input(['quantity'=>'5']));
        $payload=['warehouse_request_key'=>(string)Str::uuid(),'invoice_number'=>'1001','invoice_name'=>'Warehouse issue','total_count'=>3,'product'=>[$this->source->id],'quantity'=>[3],'source_location_id'=>$this->second->id];
        $this->post(route('saveInvoice'),$payload)->assertRedirect();$this->post(route('saveInvoice'),$payload)->assertRedirect();
        $this->assertSame(1,\App\Models\Invoice::count());$this->assertSame('42',$this->source->fresh()->total_qty);
        $this->assertSame(20000,Quantity::units(Balance::where('item_id',$this->item->id)->where('location_id',$this->second->id)->value('quantity')));
        $payload['warehouse_request_key']=(string)Str::uuid();$payload['invoice_number']='1002';$payload['product']=[$this->source->id,$this->source->id];$payload['quantity']=[1,3];
        $this->postJson(route('saveInvoice'),$payload)->assertStatus(422);
        $this->assertSame(1,\App\Models\Invoice::count());$this->assertSame('42',$this->source->fresh()->total_qty);
        $this->assertSame(1,\App\Models\InvoiceDetails::count());
        $this->post(route('saveInvoice'),$payload)->assertRedirect()->assertSessionHasErrors();
        $this->assertSame(1,\App\Models\Invoice::count());$this->assertSame('42',$this->source->fresh()->total_qty);
    }
    public function test_legacy_receipt_endpoint_and_request_replay_keep_stock_consistent():void
    {
        $doc=ProductInventory::create(['product_id'=>$this->source->id,'transit_id'=>2,'status'=>0,'stock_in'=>'10','received_stock'=>0,'remaining_stock'=>10]);
        $input=['warehouse_request_key'=>(string)Str::uuid(),'product_id'=>$doc->id,'new_quantity'=>4,'destination_location_id'=>$this->second->id];
        $this->post(route('markReceivedTransitNew'),$input)->assertRedirect();$this->post(route('markReceivedTransitNew'),$input)->assertRedirect();
        $this->assertSame('44.0000',$this->source->fresh()->total_qty);$this->assertSame(6,$doc->fresh()->remaining_stock);
        $input['warehouse_request_key']=(string)Str::uuid();$input['new_quantity']=7;$this->postJson(route('markReceivedTransitNew'),$input)->assertStatus(422);
        $this->assertSame(6,$doc->fresh()->remaining_stock);
    }
    public function test_deactivated_location_and_mismatched_total_cannot_post():void
    {
        $this->second->update(['active'=>0]);
        try{$this->inventory->post('inbound',$this->input());$this->fail('Inactive location');}catch(ValidationException $e){}
        $this->second->update(['active'=>1]);DB::table('products')->where('id',$this->source->id)->update(['total_qty'=>'99']);
        try{$this->inventory->post('inbound',$this->input());$this->fail('Aggregate drift');}catch(ValidationException $e){}
        $this->assertSame('99',$this->source->fresh()->total_qty);$this->assertSame(5,Movement::count());
    }
    public function test_simple_transfer_moves_all_or_part_once_without_changing_total_stock():void
    {
        $balance=$this->item->balances()->where('location_id',$this->loc->id)->firstOrFail();
        $input=['request_key'=>(string)Str::uuid(),'balance_id'=>$balance->id,'destination_warehouse_id'=>$this->second->warehouse_id,'destination_location_id'=>$this->second->id,'mode'=>'partial','quantity'=>'5.25'];
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertRedirect();
        $this->assertSame('34.7500',Quantity::text(Quantity::units($balance->fresh()->quantity)));
        $this->assertSame('5.2500',Quantity::text(Quantity::units($this->item->balances()->where('location_id',$this->second->id)->value('quantity'))));
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertRedirect();
        $this->assertSame(1,Transfer::count());
        $this->assertSame('completed',Transfer::first()->status);
        $input['request_key']=(string)Str::uuid();$input['mode']='all';unset($input['quantity']);
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertRedirect();
        $this->assertSame('0.0000',Quantity::text(Quantity::units($balance->fresh()->quantity)));
        $this->assertSame(400000,Quantity::units($this->item->balances()->sum('quantity')));
        $this->assertSame('40',$this->source->fresh()->total_qty);
        $this->assertSame('40',$this->source->fresh()->temp_qty);
    }
    public function test_simple_transfer_rejects_invalid_destinations_and_excess_stock():void
    {
        $balance=$this->item->balances()->where('location_id',$this->loc->id)->firstOrFail();
        $input=['request_key'=>(string)Str::uuid(),'balance_id'=>$balance->id,'destination_warehouse_id'=>$this->second->warehouse_id,'destination_location_id'=>$this->second->id,'mode'=>'partial','quantity'=>'41'];
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertStatus(422);
        $input['quantity']='1';$input['destination_warehouse_id']=$this->loc->warehouse_id;
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertStatus(422);
        $input['destination_location_id']=$this->loc->id;
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertStatus(422);
        $this->assertSame(0,Transfer::count());$this->assertSame('40',$this->source->fresh()->total_qty);
        $this->actingAs((new User)->forceFill(['id'=>98,'name'=>'Denied','role_id'=>2]));
        $this->postJson(route('warehouses.quickTransfer'),$input)->assertForbidden();
    }
    public function test_simple_warehouse_creation_includes_default_location_and_tabs_filter_stock():void
    {
        $this->post(route('warehouses.save'),['code'=>'WH3','name'=>'Warehouse 3','active'=>1,'create_default_location'=>1])->assertRedirect();
        $warehouse=Warehouse::where('code','WH3')->firstOrFail();
        $this->assertSame('1',$warehouse->locations()->first()->row);$this->assertSame('1',$warehouse->locations()->first()->column);
        $this->get(route('warehouses.index',['warehouse_id'=>$warehouse->id]))->assertOk()->assertSee('All stock')->assertSee('No matching stock in this warehouse.');
    }

    public function test_new_rows_and_columns_can_receive_a_transfer_and_warehouse_initial_location_is_customizable():void
    {
        $this->post(route('warehouses.locations'),['warehouse_id'=>$this->second->warehouse_id,'rack'=>'R1','row'=>'A','column'=>'B','active'=>1,'reorder_level'=>0])->assertRedirect();
        $location=Location::where('warehouse_id',$this->second->warehouse_id)->where('row','A')->where('column','B')->firstOrFail();
        $balance=$this->item->balances()->where('location_id',$this->loc->id)->firstOrFail();
        $this->post(route('warehouses.quickTransfer'),['request_key'=>(string)Str::uuid(),'balance_id'=>$balance->id,'destination_warehouse_id'=>$this->second->warehouse_id,'destination_location_id'=>$location->id,'mode'=>'partial','quantity'=>'2.5'])->assertRedirect();
        $this->assertSame(25000,Quantity::units($this->item->balances()->where('location_id',$location->id)->value('quantity')));
        $this->assertSame(375000,Quantity::units($balance->fresh()->quantity));
        $this->post(route('warehouses.save'),['code'=>'CUSTOM','name'=>'Custom location','active'=>1,'create_default_location'=>1,'initial_row'=>'North','initial_column'=>'10'])->assertRedirect();
        $warehouse=Warehouse::where('code','CUSTOM')->firstOrFail();
        $this->assertTrue($warehouse->locations()->where('row','1')->where('column','1')->exists());$this->assertTrue($warehouse->locations()->where('row','North')->where('column','10')->exists());
    }

    public function test_every_new_warehouse_has_a_required_default_location_without_an_opt_in_flag():void
    {
        $this->post(route('warehouses.save'),['code'=>'DEFAULT','name'=>'Default warehouse','active'=>1])->assertRedirect();
        $warehouse=Warehouse::where('code','DEFAULT')->firstOrFail();$location=$warehouse->locations()->where('rack','')->where('row','1')->where('column','1')->firstOrFail();
        $input=['id'=>$location->id,'warehouse_id'=>$warehouse->id,'rack'=>'','row'=>'2','column'=>'1','active'=>1,'reorder_level'=>0];
        $this->postJson(route('warehouses.locations'),$input)->assertStatus(422);
        $input['row']='1';$input['active']=0;
        $this->postJson(route('warehouses.locations'),$input)->assertStatus(422);
        $this->assertSame('1',$location->fresh()->row);$this->assertTrue((bool)$location->fresh()->active);
    }

    private function excludedProduct(): Products
    {
        $id=DB::table('products')->insertGetId(['name'=>'Unassigned battery','sku'=>'NEG','type'=>'Inventory','total_qty'=>'-2','temp_qty'=>'-6']);
        $this->artisan('warehouses:backfill',['--apply'=>true,'--skip-negative-products'=>true])->assertExitCode(0);
        return Products::findOrFail($id);
    }
    public function test_negative_products_can_be_excluded_without_changing_stock_or_duplicating_history():void
    {
        $product=$this->excludedProduct();
        $this->assertSame('-2',$product->total_qty);
        $this->assertFalse(Item::where('source_type','product')->where('source_id',$product->id)->exists());
        $this->assertSame('-2.0000',DB::table('warehouse_state')->where('key','unassigned:product:'.$product->id)->value('value'));
        $this->artisan('warehouses:backfill',['--apply'=>true,'--skip-negative-products'=>true])->assertExitCode(0);
        $this->assertSame(1,DB::table('warehouse_operations')->where('type','legacy-unassigned')->count());
        $this->assertSame(5,Movement::count());
        try{$this->inventory->atomic(function()use($product){$product->total_qty='2';$product->save();});$this->fail('Unassigned stock write allowed');}catch(ValidationException $error){$this->assertStringContainsString('unassigned',implode(' ',array_merge(...array_values($error->errors()))));}
        $this->assertSame('-2',$product->fresh()->total_qty);
        $this->get(route('warehouses.unassigned'))->assertOk()->assertSee('Unassigned battery')->assertSee('Actual physical stock');
    }
    public function test_excluded_product_correction_assigns_once_and_preserves_projected_difference():void
    {
        $product=$this->excludedProduct();
        $input=['request_key'=>(string)Str::uuid(),'product_id'=>$product->id,'warehouse_id'=>$this->second->warehouse_id,'location_id'=>$this->second->id,'quantity'=>'3.25','reference'=>'Physical count'];
        $op=$this->inventory->assignUnassignedProduct($input);
        $this->assertSame('3.2500',$product->fresh()->total_qty);
        $this->assertSame('-0.7500',$product->fresh()->temp_qty);
        $this->assertSame('-2.0000',$op->result['previous_quantity']);
        $this->assertFalse(DB::table('warehouse_state')->where('key','unassigned:product:'.$product->id)->exists());
        $item=Item::where('source_type','product')->where('source_id',$product->id)->firstOrFail();
        $this->assertSame('3.2500',Quantity::text(Quantity::units($item->balances()->sum('quantity'))));
        $this->assertSame($op->id,$this->inventory->assignUnassignedProduct($input)->id);
        $this->assertSame(1,$item->movements()->count());
        $this->assertSame($this->second->id,(int)$item->balances()->first()->location_id);
    }
    public function test_unassigned_correction_rejects_wrong_location_and_unauthorized_actor():void
    {
        $product=$this->excludedProduct();
        $input=['request_key'=>(string)Str::uuid(),'product_id'=>$product->id,'warehouse_id'=>$this->loc->warehouse_id,'location_id'=>$this->second->id,'quantity'=>'1','reference'=>'Count'];
        $this->postJson(route('warehouses.assignUnassigned'),$input)->assertStatus(422);
        $this->assertSame('-2',$product->fresh()->total_qty);
        $this->assertFalse(Item::where('source_type','product')->where('source_id',$product->id)->exists());
        $this->actingAs((new User)->forceFill(['id'=>98,'name'=>'Read only','role_id'=>3]));
        $this->postJson(route('warehouses.assignUnassigned'),$input)->assertForbidden();
    }

    public function test_negative_legacy_preflight_refuses_to_change_stock():void
    {
        DB::table('products')->where('id',$this->source->id)->update(['total_qty'=>'-1']);
        $this->artisan('warehouses:backfill',['--apply'=>true])->assertExitCode(1);
        $this->assertSame('-1',$this->source->fresh()->total_qty);$this->assertSame(5,Movement::count());
    }

    public function test_legacy_recipe_production_uses_selected_materials_and_destination():void
    {
        Schema::create('semi_raw_material_inventories',function(Blueprint $t){$t->id();$t->integer('semi_raw_material_id');$t->string('stock_in');$t->string('remarks');$t->integer('transit_id');$t->integer('status');$t->timestamps();});
        Schema::create('semi_raw_material_recipes',function(Blueprint $t){$t->id();$t->integer('raw_material_id');$t->integer('semi_raw_material_id');$t->decimal('qty',20,4);$t->timestamps();});
        $raw=RawMaterial::first();$semi=SemiRawMaterial::first();
        \App\Models\SemiRawMaterialRecipe::create(['raw_material_id'=>$raw->id,'semi_raw_material_id'=>$semi->id,'qty'=>2.5]);
        $this->getJson(route('warehouses.productionAvailability',['recipe_type'=>'semi','recipe_id'=>$semi->id,'warehouse_ids'=>[$this->loc->warehouse_id]]))->assertOk()->assertJsonPath('materials.0.quantity','20.0000');
        $input=['warehouse_request_key'=>(string)Str::uuid(),'machine_id'=>$semi->id,'machine_qty'=>2,'raw_material_id'=>[$raw->id],'qty_old'=>[5],'source_warehouse_ids'=>[$this->loc->warehouse_id],'destination_location_id'=>$this->second->id];
        $this->post(route('saveSemiRawMaterialProduction'),$input)->assertRedirect();
        $this->post(route('saveSemiRawMaterialProduction'),$input)->assertRedirect();
        $this->assertSame('15',$raw->fresh()->current_stock);$this->assertSame('5',$semi->fresh()->current_stock);
        $semiItem=Item::where('source_type','semi')->first();$this->assertSame(20000,Quantity::units(Balance::where('item_id',$semiItem->id)->where('location_id',$this->second->id)->value('quantity')));
        $this->assertSame(1,\App\Models\SemiRawMaterialInventory::count());
        $this->assertSame(2,\App\Models\Warehouse\Operation::where('reference','semi_raw_material_inventories:1')->count());
        $input['warehouse_request_key']=(string)Str::uuid();$input['source_warehouse_ids']=[$this->second->warehouse_id];
        $this->postJson(route('saveSemiRawMaterialProduction'),$input)->assertStatus(422);
        $this->assertSame(1,\App\Models\SemiRawMaterialInventory::count());$this->assertSame('5',$semi->fresh()->current_stock);
    }
    public function test_filtered_stock_export_and_transfer_views_render_without_stock_writes():void
    {
        $before=Movement::count();
        $csv=$this->get(route('warehouses.index',['source_type'=>'raw','csv'=>1]))->assertOk()->streamedContent();
        $this->get(route('warehouses.index',['source_type'=>'raw','print'=>1]))->assertOk()->assertSee('Copper')->assertDontSee('Battery')->assertSee('All matching records across all pages.');
        $this->get(route('warehouses.history',['print'=>1]))->assertOk()->assertSee('Warehouse movement ledger')->assertSee('Legacy warehouse');
        $this->assertStringContainsString('Copper',$csv);$this->assertStringNotContainsString('Battery',$csv);
        $this->get(route('warehouses.transfers'))->assertOk()->assertSee('No transfers yet.');
        $this->getJson(route('warehouses.availability',['item_id'=>$this->item->id,'location_id'=>$this->loc->id]))->assertOk()->assertJsonPath('quantity','40');
        $this->getJson(route('warehouses.context'))->assertOk()->assertJsonStructure(['warehouses','locations','actions']);
        $this->assertSame($before,Movement::count());
    }

    public function test_warehouse_specific_low_stock_thresholds_use_combined_locations():void
    {
        $this->post(route('warehouses.reorder'),['item_id'=>$this->item->id,'warehouse_id'=>$this->loc->warehouse_id,'quantity'=>'50'])->assertRedirect();
        $this->get(route('warehouses.index'))->assertOk()->assertSee('1 low-stock SKUs');
        $this->get(route('warehouses.index',['low'=>1]))->assertOk()->assertViewHas('balances',fn($balances)=>$balances->count()===1 && $balances->first()->item_id===$this->item->id);
        $this->assertSame('40',$this->source->fresh()->total_qty);
    }

    public function test_zero_stock_skus_have_a_location_without_duplicate_opening_movements():void
    {
        $source=$this->inventory->atomic(fn()=>Products::create(['name'=>'Zero stock item','type'=>'Inventory','total_qty'=>'0','temp_qty'=>'0']));
        $item=Item::where('source_type','product')->where('source_id',$source->id)->firstOrFail();
        $this->assertSame(1,$item->balances()->count());$this->assertSame(0,Quantity::units($item->balances()->sum('quantity')));
        $this->assertSame(0,$item->movements()->count());
        $this->artisan('warehouses:backfill',['--apply'=>true])->assertExitCode(0);
        $this->assertSame(1,$item->balances()->count());
    }

    public function test_material_precision_and_unmanaged_stock_writes_are_rejected_without_partial_records():void
    {
        $rawItem=Item::where('source_type','raw')->first();
        try{$this->inventory->post('inbound',$this->input(['item_id'=>$rawItem->id,'quantity'=>'1.001']));$this->fail('Unsupported material precision.');}catch(ValidationException $e){}
        $this->assertSame(5,Movement::count());
        $this->inventory->post('inbound',$this->input(['item_id'=>$rawItem->id,'quantity'=>'1.25']));
        $this->assertSame(212500,Quantity::units(RawMaterial::first()->current_stock));
        try{RawMaterial::create(['name'=>'Unmanaged stock','current_stock'=>'1','temp_stock'=>'1']);$this->fail('Unmanaged write.');}catch(ValidationException $e){}
        $this->assertSame(1,RawMaterial::count());
        try{DB::transaction(fn()=>RawMaterial::first()->update(['current_stock'=>'1']));$this->fail('Unmanaged transaction.');}catch(ValidationException $e){}
        $this->assertSame(212500,Quantity::units(RawMaterial::first()->current_stock));
    }

    public function test_legacy_get_stock_actions_require_session_csrf_header():void
    {
        $doc=ProductInventory::create(['product_id'=>$this->source->id,'transit_id'=>2,'status'=>0,'stock_in'=>'10','received_stock'=>0,'remaining_stock'=>10]);
        $parameters=['id'=>$doc->id,'warehouse_request_key'=>(string)Str::uuid()];
        $this->withSession(['_token'=>'warehouse-csrf-test'])->get(route('markReceivedTransit',$parameters))->assertStatus(419);
        $this->assertSame('40',$this->source->fresh()->total_qty);
        $this->withSession(['_token'=>'warehouse-csrf-test'])->get(route('markReceivedTransit',$parameters),['X-CSRF-TOKEN'=>'warehouse-csrf-test'])->assertRedirect(route('receivedTransitInventory'));
        $this->assertSame('50.0000',$this->source->fresh()->total_qty);
    }

    public function test_disabling_the_ui_cannot_bypass_an_activated_ledger():void
    {
        config(['warehouses.enabled'=>false]);
        $this->postJson(route('saveInvoice'),['invoice_number'=>'unsafe'])->assertStatus(503);
        try{RawMaterial::create(['name'=>'Bypass','current_stock'=>'1']);$this->fail('Inactive switch bypass.');}catch(ValidationException $e){}
        $this->assertSame(1,RawMaterial::count());$this->assertSame('40',$this->source->fresh()->total_qty);
    }

}
