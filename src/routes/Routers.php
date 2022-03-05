<?php

namespace TestApp\Routes;

use TestApp\Core\Router;
use TestApp\Controllers\AuthController;
use TestApp\Controllers\CategoryController;
use TestApp\Controllers\HomeController;
use TestApp\Controllers\ProfileController;

$homeRouter = new Router("/");
$homeRouter
  ->addRoute("home", [
    "path" => "/",
    "methods" => [
      "GET" => [HomeController::class, "index"]
    ]
  ])
  ->addRoute("about", [
    "path" => "/a-propos",
    "methods" => [
      "GET" => [HomeController::class, "about"]
    ]
  ]);

$authRouter = new Router("/auth");
$authRouter
  ->addRoute("sign-in", [
    "path" => "/connexion",
    "methods" => [
      "GET" => [AuthController::class, "signIn_GET"],
      "POST" => [AuthController::class, "signIn_POST"]
    ]
  ])
  ->addRoute("sign-up", [
    "path" => "/inscription",
    "methods" => [
      "GET" => [AuthController::class, "signUp_GET"],
      "POST" => [AuthController::class, "signUp_POST"]
    ]
  ])
  ->addRoute("sign-out", [
    "path" => "/deconnexion",
    "methods" => [
      "GET" => [AuthController::class, "signOut"]
    ]
  ])
  ->addRoute("activate-account", [
    "path" => "/activation-compte/:verifString",
    "methods" => [
      "GET" => [AuthController::class, "activateAccount"]
    ]
  ]);

$profileRouter = new Router("/profil");
$profileRouter->addRoute("profile-home", [
  "path" => "/:username",
  "methods" => [
    "GET" => [ProfileController::class, "index"]
  ]
]);

$categoryRouter = new Router("/categories");
$categoryRouter
  ->addRoute("categories", [
    "path" => "/",
    "methods" => [
      "GET" => [CategoryController::class, "all"]
    ]
  ])
  ->addRoute("category", [
    "path" => "/:category",
    "methods" => [
      "GET" => [CategoryController::class, "single"]
    ]
  ]);
