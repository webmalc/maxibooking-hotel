<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
mb_internal_encoding('utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();
$request = Request::createFromGlobals();
$client = $request->server->get('MBCLIENT');

$kernel = new AppKernel('dev', true, $client);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);