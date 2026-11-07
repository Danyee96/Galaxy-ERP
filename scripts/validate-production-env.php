<?php
$deploymentFail=static function(string $message): void {fwrite(STDERR,$message.PHP_EOL);exit(1);};
try {
$base=$argv[1]??'';
require $base.'/vendor/autoload.php';
$values=Dotenv\Dotenv::parse(file_get_contents($base.'/.env'));
foreach(['APP_ENV'=>'production','APP_DEBUG'=>'false'] as $key=>$expected) {
    if(strtolower(trim($values[$key]??''))!==$expected) {fwrite(STDERR,'Production configuration must define '.$key.'='.$expected.'. Existing .env was not changed.'.PHP_EOL);exit(1);}
}
if(empty($values['APP_KEY'])) {fwrite(STDERR,'Provision the existing production APP_KEY before deployment; it will not be generated.'.PHP_EOL);exit(1);}
$warehouseFlag=strtolower(trim($values['WAREHOUSES_ENABLED']??''));
if(!in_array($warehouseFlag,['true','false'],true))$deploymentFail('Define WAREHOUSES_ENABLED as true or false in the private environment.');
if($warehouseFlag==='true') {
    $app=require $base.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if(!Illuminate\Support\Facades\Schema::hasTable('warehouse_state') || Illuminate\Support\Facades\DB::table('warehouse_state')->where('key','ready')->value('value')!=='1')$deploymentFail('Warehouse activation requires an initialized, reconciled warehouse ledger.');
}
echo 'Private production environment validated without displaying values.'.PHP_EOL;

} catch (Throwable $error) {fwrite(STDERR,"Deployment validation failed (".get_class($error)."). Check private application logs.\n");exit(1);}
