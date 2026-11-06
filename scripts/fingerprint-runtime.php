<?php
$domain=rtrim($argv[1]??'','/');$output=$argv[2]??'';$app=$domain.'/laravel_app';
$result=['env'=>hash_file('sha256',$app.'/.env'),'uploads'=>[]];
foreach(['laravel_app/storage/app','laravel_app/public/uploads','laravel_app/public/upload','public_html/uploads','public_html/upload'] as $relative){$root=$domain.'/'.$relative;if(!is_dir($root))continue;$files=[];$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));foreach($iterator as $file){if($file->isFile()&&!$file->isLink())$files[substr($file->getPathname(),strlen($root)+1)]=hash_file('sha256',$file->getPathname());}ksort($files);$result['uploads'][$relative]=$files;}
file_put_contents($output,json_encode($result,JSON_THROW_ON_ERROR));chmod($output,0600);echo 'Private environment and upload fingerprints recorded; no values displayed.'.PHP_EOL;
