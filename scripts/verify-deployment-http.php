<?php
$base=rtrim($argv[1]??'','/');
if(!preg_match('#^https?://#',$base))throw new RuntimeException('Provide the complete website URL.');
foreach(['/login'=>[200,301,302,303,307,308],'/css/bootstrap.css'=>[200],'/brand/erp.js'=>[200],'/.env'=>[403,404],'/vendor/autoload.php'=>[403,404]] as $path=>$allowed){
    $curl=curl_init($base.$path);$bytes=0;
    curl_setopt_array($curl,[CURLOPT_TIMEOUT=>20,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_WRITEFUNCTION=>function($c,$data)use(&$bytes){$bytes+=strlen($data);return strlen($data);},CURLOPT_USERAGENT=>'Galaxy-ERP-Deployment-Check']);
    $ok=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_HTTP_CODE);$type=curl_getinfo($curl,CURLINFO_CONTENT_TYPE);curl_close($curl);
    if($ok===false||!in_array($status,$allowed,true))throw new RuntimeException('HTTP verification failed for '.$path.' (status '.$status.').');
    if(str_ends_with($path,'.css') && strpos($type??'','text/css')===false)throw new RuntimeException('CSS URL did not serve CSS.');
    if(str_ends_with($path,'.js') && strpos($type??'','javascript')===false)throw new RuntimeException('JavaScript URL did not serve JavaScript.');
    echo 'PASS '.$path.' HTTP '.$status.PHP_EOL;
}

// Short-lived, unguessable probe checks the actual web PHP/configuration; no secrets.
if(!empty($argv[2])) {
    $expectedWarehouses=($argv[3]??'false')==='true';
    $web=rtrim($argv[2],'/');$name='galaxy-health-'.bin2hex(random_bytes(16)).'.php';$path=$web.'/'.$name;
    $code='<?php require __DIR__."/../laravel_app/vendor/autoload.php"; $app=require __DIR__."/../laravel_app/bootstrap/app.php"; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); header("Content-Type: application/json"); echo json_encode(["php"=>PHP_VERSION_ID,"sodium"=>extension_loaded("sodium"),"dom"=>extension_loaded("dom"),"fileinfo"=>extension_loaded("fileinfo"),"pdo_mysql"=>extension_loaded("pdo_mysql"),"debug"=>config("app.debug"),"env"=>config("app.env"),"warehouses"=>config("warehouses.enabled")]);';
    try {
        file_put_contents($path,$code);chmod($path,0644);
        $c=curl_init($base.'/'.$name);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);$body=curl_exec($c);$status=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
        $data=json_decode($body?:'',true);
        if($status!==200||!is_array($data)||($data['php']??0)<80200||$data['php']>=80300||!$data['sodium']||!$data['dom']||!$data['fileinfo']||!$data['pdo_mysql']||$data['debug']||$data['env']!=='production'||((bool)$data['warehouses']!==$expectedWarehouses))throw new RuntimeException('Web runtime must be PHP 8.2 with required extensions and production flags.');
        echo 'PASS real web PHP 8.2, Sodium/DOM/Fileinfo/PDO MySQL and production flags.'.PHP_EOL;
    } finally {if(is_file($path))unlink($path);}
}
