<?php
$public=rtrim($argv[1]??'', '/');
$paths=['index.php','.htaccess','mix-manifest.json','css/bootstrap.css','css/components.css','vendors/css/vendors.min.css','vendors/js/vendors.min.js','brand/erp.js','brand/erp.css','assets/css/biztally.css','assets/js/biztally.js'];
foreach($paths as $path)if(!is_file($public.'/'.$path)||filesize($public.'/'.$path)===0){fwrite(STDERR,'Missing real compiled public asset: '.$path.PHP_EOL);exit(1);}
$manifest=json_decode(file_get_contents($public.'/mix-manifest.json'),true,512,JSON_THROW_ON_ERROR);
foreach($manifest as $path=>$url){if(!is_file($public.'/'.ltrim($path,'/'))||strpos($url,'/demo/')!==false){fwrite(STDERR,'Invalid production asset manifest entry: '.$path.PHP_EOL);exit(1);}}
if(strpos(file_get_contents($public.'/.htaccess'),'lsphp80')!==false)throw new RuntimeException('Obsolete PHP 8.0 handler.');
echo 'Real compiled assets and manifest verified.'.PHP_EOL;
