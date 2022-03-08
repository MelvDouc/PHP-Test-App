<?php

namespace TestApp\Routes;

use TestApp\Controllers\AdminController;
use TestApp\Core\Router;
use TestApp\Controllers\AuthController;
use TestApp\Controllers\CategoryController;
use TestApp\Controllers\HomeController;
use TestApp\Controllers\ProductController;
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
      "GET" => [
        [AuthController::class, "redirectUserIfSignedIn"],
        [AuthController::class, "signIn_GET"]
      ],
      "POST" => [AuthController::class, "signIn_POST"]
    ]
  ])
  ->addRoute("sign-up", [
    "path" => "/inscription",
    "methods" => [
      "GET" => [
        [AuthController::class, "redirectUserIfSignedIn"],
        [AuthController::class, "signUp_GET"]
      ],
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
    "path" => "/activation-compte/:verif_string",
    "methods" => [
      "GET" => [
        [AuthController::class, "redirectUserIfSignedIn"],
        [AuthController::class, "activateAccount"]
      ]
    ]
  ]);

$profileRouter = new Router("/profil");
$profileRouter
  ->addRoute("profile-home", [
    "path" => "/:username",
    "methods" => [
      "GET" => [ProfileController::class, "index"]
    ]
  ])
  ->addRoute("user-products", [
    "path" => "/:username/articles",
    "methods" => [
      "GET" => [ProfileController::class, "products"]
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

$productRouter = new Router("/articles");
$productRouter
  ->addRoute("add-product", [
    "path" => "/ajouter",
    "methods" => [
      "GET" => [ProductController::class, "add_GET"],
      "POST" => [ProductController::class, "add_POST"]
    ]
  ])
  ->addRoute("update-product", [
    "path" => "/modifier/:slug",
    "methods" => [
      "GET" => [
        [ProductController::class, "setProduct"],
        [ProductController::class, "update_GET"]
      ],
      "POST" => [ProductController::class, "update_POST"]
    ]
  ])
  ->addRoute("delete-product", [
    "path" => "/supprimer/:id",
    "methods" => [
      "POST" => [
        [AdminController::class, "setForbiddenIfNotAdmin"],
        [ProductController::class, "delete"]
      ]
    ]
  ])
  ->addRoute("product", [
    "path" => "/:slug",
    "methods" => [
      "GET" => [
        [ProductController::class, "setProduct"],
        [ProductController::class, "single"]
      ]
    ]
  ]);

$adminRouter = new Router("/admin");
$adminRouter
  ->addMiddleware([AdminController::class, "setForbiddenIfNotAdmin"])
  ->addRoute("admin-home", [
    "path" => "/",
    "methods" => [
      "GET" => [AdminController::class, "home"]
    ]
  ])
  ->addRoute("admin-users-list", [
    "path" => "/utilisateurs",
    "methods" => [
      "GET" => [AdminController::class, "usersList"]
    ]
  ])
  ->addRoute("admin-products-list", [
    "path" => "/articles",
    "methods" => [
      "GET" => [AdminController::class, "productsList"]
    ]
  ]);
