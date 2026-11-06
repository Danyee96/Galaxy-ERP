<?php
$base=$argv[1]??'';$output=$argv[2]??'';
require $base.'/vendor/autoload.php';$app=require $base.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db=Illuminate\Support\Facades\DB::connection();$result=[];
$db->beginTransaction();
try {
    $tables=$db->getDriverName()==='sqlite'?$db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"):$db->select('SHOW TABLES');
    foreach($tables as $entry){$table=array_values((array)$entry)[0];$rows=[];foreach($db->table($table)->cursor() as $row){$row=(array)$row;ksort($row);$rows[]=hash('sha256',serialize($row));}sort($rows,SORT_STRING);$result[$table]=['rows'=>count($rows),'hash'=>hash('sha256',implode('',$rows))];}
    ksort($result);file_put_contents($output,json_encode($result,JSON_THROW_ON_ERROR));chmod($output,0600);$db->rollBack();
    echo 'Read-only database fingerprint recorded for '.count($result).' tables; no values displayed.'.PHP_EOL;
} catch(Throwable $error){$db->rollBack();throw $error;}
