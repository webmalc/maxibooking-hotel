<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
mb_internal_encoding('utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//if (isset($_SERVER['HTTP_CLIENT_IP'])
 //   || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
//    || !(in_array(@$_SERVER['REMOTE_ADDR'], array('94.159.1.194','127.0.0.1', '172.17.0.1', 'fe80::1', '::1')) || PHP_SAPI === 'cli-server')
//) {
//    header('HTTP/1.0 403 Forbidden');
//    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();
$kernel = new AppKernel('dev', true);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
