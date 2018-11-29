<?php

use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasChecker;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

mb_internal_encoding('utf-8');
umask(0000);


/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
if (PHP_VERSION_ID < 70000) {
    include_once __DIR__.'/../var/bootstrap.php.cache';
}
$env = 'prod';
AliasChecker::checkAlias(AppKernel::CLIENT_VARIABLE, $env);
$request = Request::createFromGlobals();
Request::setTrustedProxies(['127.0.0.1', $request->server->get('REMOTE_ADDR') ], Request::HEADER_X_FORWARDED_AWS_ELB);

//Note! Default client comes  here from NGINX config
$client = $request->server->get(AppKernel::CLIENT_VARIABLE);
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../app/config/database.env', __DIR__.'/../app/config/clients/'.$client.'.env');

$kernel = new AppKernel($env, false, $client);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}
//$kernel = new AppCache($kernel);
// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);