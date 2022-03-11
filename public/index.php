<?php

use TestApp\Core\Application;

session_start();

require_once "../vendor/autoload.php";
require_once "../src/routes/Routers.php";

// The project directory, useful for navigating files and folders.
$rootDir = dirname(__DIR__);

// Global environment variables such as the DB connection credentials.
$dotenv = \Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->load();

$app = new Application($rootDir);

// The various routers holding all of the application's routes.
$app
  ->useRouters(
    $homeRouter,
    $authRouter,
    $profileRouter,
    $categoryRouter,
    $productRouter,
    $adminRouter
  );

// Execute the appropriate controller action based on the current path and HTTP method.
$app->run();
