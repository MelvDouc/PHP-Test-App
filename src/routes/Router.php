<?php

namespace TestApp\Routes;

use TestApp\Core\Router;
use TestApp\Controllers\AuthController;
use TestApp\Controllers\CategoryController;
use TestApp\Controllers\HomeController;
use TestApp\Controllers\ProfileController;

$homeRouter = new Router("/");
$homeRouter->setRoutes([
  "home" => [
    "path" => "/",
    "methods" => [
      "GET" => [HomeController::class, "index"]
    ]
  ],
  "about" => [
    "path" => "/a-propos",
    "methods" => [
      "GET" => [HomeController::class, "about"]
    ]
  ]
]);

$authRouter = new Router("/auth");
$authRouter->setRoutes([
  "sign-in" => [
    "path" => "/connexion",
    "methods" => [
      "GET" => [AuthController::class, "signIn_GET"],
      "POST" => [AuthController::class, "signIn_POST"]
    ]
  ],
  "sign-up" => [
    "path" => "/inscription",
    "methods" => [
      "GET" => [AuthController::class, "signUp_GET"],
      "POST" => [AuthController::class, "signUp_POST"]
    ]
  ],
  "sign-out" => [
    "path" => "/deconnexion",
    "methods" => [
      "GET" => [AuthController::class, "signOut"]
    ]
  ],
  "activate-account" => [
    "path" => "/activation-compte",
    "methods" => [
      "GET" => [AuthController::class, "activateAccount"]
    ]
  ]
]);

$profileRouter = new Router("/profil");
$profileRouter->setRoutes([
  "profile-home" => [
    "path" => "/:id",
    "methods" => [
      "GET" => [ProfileController::class, "index"]
    ]
  ]
]);

$categoryRouter = new Router("/categories");
$categoryRouter->setRoutes([
  "categories" => [
    "path" => "/",
    "methods" => [
      "GET" => [CategoryController::class, "index"]
    ]
  ]
]);
