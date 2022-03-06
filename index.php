<?php

use TestApp\Core\Application;

session_start();

require_once "./vendor/autoload.php";
require_once "./src/routes/Routers.php";

$rootDir = __DIR__;

$dotenv = \Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->load();

$app = new Application($rootDir);

$app
  ->useRouter($homeRouter)
  ->useRouter($authRouter)
  ->useRouter($profileRouter)
  ->useRouter($categoryRouter)
  ->useRouter($productRouter)
  ->useRouter($adminRouter);

$app->run();
