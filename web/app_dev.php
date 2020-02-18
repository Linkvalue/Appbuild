<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

if($_ENV['APP_ENV'] !== 'prod') {
    Debug::enable();
}

$kernel = new AppKernel($_ENV['APP_ENV'], true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
