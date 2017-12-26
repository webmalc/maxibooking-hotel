<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

mb_internal_encoding('utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();
Request::setTrustedProxies(['127.0.0.1', '127.0.1.1', '176.192.20.30'], Request::HEADER_X_FORWARDED_ALL);
$request = Request::createFromGlobals();
$client = $request->server->get(AppKernel::CLIENT_VARIABLE) ?: AppKernel::DEFAULT_CLIENT;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../app/config/database.env', __DIR__.'/../app/config/clients/'.$client.'.env');

$kernel = new AppKernel('dev', true, $client);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);