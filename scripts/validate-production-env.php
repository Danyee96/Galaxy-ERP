<?php
$base=$argv[1]??'';
require $base.'/vendor/autoload.php';
$values=Dotenv\Dotenv::parse(file_get_contents($base.'/.env'));
foreach(['APP_ENV'=>'production','APP_DEBUG'=>'false','WAREHOUSES_ENABLED'=>'false'] as $key=>$expected) {
    if(strtolower(trim($values[$key]??''))!==$expected) {fwrite(STDERR,'Production configuration must define '.$key.'='.$expected.'. Existing .env was not changed.'.PHP_EOL);exit(1);}
}
if(empty($values['APP_KEY'])) {fwrite(STDERR,'Provision the existing production APP_KEY before deployment; it will not be generated.'.PHP_EOL);exit(1);}
echo 'Private production environment validated without displaying values.'.PHP_EOL;
