<?php

use Symfony\Component\HttpFoundation\Request;
use App\Core\Application;

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);

$request = Request::createFromGlobals();

$application  = new Application();

$response = $application->handle($request);

$response->send();
