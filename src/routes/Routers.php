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
  ->route("home", "/")
  ->get([HomeController::class, "index"]);
$homeRouter
  ->route("about", "/a-propos")
  ->get([HomeController::class, "about"]);

//

$authRouter = new Router("/auth");
$authRouter
  ->route("sign-in", "/connexion")
  ->get(
    [AuthController::class, "redirectUserIfSignedIn"],
    [AuthController::class, "signIn_GET"]
  )
  ->post([AuthController::class, "signIn_POST"]);
$authRouter
  ->route("sign-up", "/inscription")
  ->get(
    [AuthController::class, "redirectUserIfSignedIn"],
    [AuthController::class, "signUp_GET"]
  )
  ->post([AuthController::class, "signUp_POST"]);
$authRouter
  ->route("sign-out", "/deconnexion")
  ->get([AuthController::class, "signOut"]);
$authRouter
  ->route("activate-account", "/activation-compte/:verif_string")
  ->get(
    [AuthController::class, "redirectUserIfSignedIn"],
    [AuthController::class, "activateAccount"]
  );

//

$profileRouter = new Router("/profil");
$profileRouter
  ->route("profile-home", "/:username")
  ->get([ProfileController::class, "index"]);
$profileRouter
  ->route("user-products", "/:username/products")
  ->get([ProfileController::class, "products"]);


//

$categoryRouter = new Router("/categories");
$categoryRouter
  ->route("categories", "/")
  ->get([CategoryController::class, "all"]);
$categoryRouter
  ->route("category", "/:category")
  ->get([CategoryController::class, "single"]);

//

$productRouter = new Router("/articles");
$productRouter
  ->route("add-product", "/ajouter")
  ->middleware([AuthController::class, "checkIfUserSignedIn"])
  ->get([ProductController::class, "add_GET"])
  ->post([ProductController::class, "add_POST"]);
$productRouter
  ->route("update-product", "/modifier/:slug")
  ->middleware([AuthController::class, "checkIfUserSignedIn"])
  ->get(
    [ProductController::class, "setProduct"],
    [ProductController::class, "update_GET"]
  )
  ->post([ProductController::class, "update_POST"]);
$productRouter
  ->route("delete-product", "/supprimer/:id")
  ->post(
    [AdminController::class, "setForbiddenIfNotAdmin"],
    [ProductController::class, "delete"]
  );
$productRouter
  ->route("product", "/:slug")
  ->get(
    [ProductController::class, "setProduct"],
    [ProductController::class, "single"]
  );

//

$adminRouter = new Router("/admin");
$adminRouter
  ->addMiddleware([AdminController::class, "setForbiddenIfNotAdmin"]);
$adminRouter
  ->route("admin-home", "/")
  ->get([AdminController::class, "home"]);
$adminRouter
  ->route("admin-users-list", "/utilisateurs")
  ->get([AdminController::class, "usersList"]);
$adminRouter
  ->route("admin-products-list", "/articles")
  ->get([AdminController::class, "productsList"]);
