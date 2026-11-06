<?php
$domain=rtrim($argv[1]??'','/');$web=$domain.'/public_html';$base=$domain.'/laravel_app';
foreach(['.env','vendor/autoload.php','bootstrap/app.php','artisan'] as $path)if(!is_file($base.'/'.$path))throw new RuntimeException('Incomplete private application layout.');
foreach(['.env','.git','app','bootstrap','config','database','resources','routes','vendor','artisan','composer.json','public'] as $name)if(file_exists($web.'/'.$name)||is_link($web.'/'.$name))throw new RuntimeException('Private or nested deployment entry present in the public web root: '.$name);
require $base.'/vendor/autoload.php';$app=require $base.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(realpath(public_path())!==realpath($web))throw new RuntimeException('Laravel public_path does not match public_html.');
if(config('app.env')!=='production'||config('app.debug')||config('warehouses.enabled'))throw new RuntimeException('Unsafe production configuration.');
if(!is_writable($base.'/storage')||!is_writable($base.'/bootstrap/cache'))throw new RuntimeException('Private runtime directories are not writable.');
echo 'PASS private layout, public_path, production flags and writable runtime directories.'.PHP_EOL;
