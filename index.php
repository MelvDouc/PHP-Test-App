<?php

use TestApp\Core\Application;

session_start();

require_once "./vendor/autoload.php";
require_once "./src/routes/Routers.php";

$app = new Application(__DIR__);

$app->useRouter($homeRouter);
$app->useRouter($authRouter);
$app->useRouter($profileRouter);
$app->useRouter($categoryRouter);
$app->run();
