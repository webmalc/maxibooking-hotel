<?php

use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasChecker;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

mb_internal_encoding('utf-8');
umask(0000);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
/** @noinspection ForgottenDebugOutputInspection */
Debug::enable();
$env = 'dev';
AliasChecker::checkAlias(AppKernel::CLIENT_VARIABLE, $env);
$request = Request::createFromGlobals();
Request::setTrustedProxies(['127.0.0.1', '127.0.1.1', $request->server->get('REMOTE_ADDR')], Request::HEADER_X_FORWARDED_AWS_ELB);
if (!\in_array($request->getClientIp(), ['94.159.1.194', '127.0.0.1', '127.0.1.1', '172.17.0.1'], true)) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

//Note! Default client comes  here from NGINX config
$client = $request->server->get(AppKernel::CLIENT_VARIABLE);
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../app/config/database.env', __DIR__.'/../app/config/clients/'.$client.'.env');

$kernel = new AppKernel($env, true, $client);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);