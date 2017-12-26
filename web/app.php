<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

mb_internal_encoding('utf-8');

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
if (PHP_VERSION_ID < 70000) {
    include_once __DIR__.'/../var/bootstrap.php.cache';
}
Request::setTrustedProxies(['127.0.0.1', '127.0.1.1', '176.192.20.30'], Request::HEADER_X_FORWARDED_ALL);
$request = Request::createFromGlobals();
$client = $request->server->get(AppKernel::CLIENT_VARIABLE) ?: AppKernel::DEFAULT_CLIENT;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../app/config/database.env', __DIR__.'/../app/config/clients/'.$client.'.env');

$kernel = new AppKernel('prod', false, $client);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}
//$kernel = new AppCache($kernel);
// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);