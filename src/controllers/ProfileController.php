<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class ProfileController
{
  // Middleware
  public static function getUser(Request $req, Response $res)
  {
    $user = User::findOne([
      "username" => $req->getParam("username")
    ]);

    if (!$user)
      return $res->redirectNotFound();

    $req->setMiddlewareData("user", $user);
  }

  public static function index(Request $req, Response $res)
  {
    $user = $req->getMiddlewareData("user");

    $res->render("profile/index", [
      "username" => $user->getUsername()
    ]);
  }

  public static function products(Request $req, Response $res)
  {
    $user = $req->getMiddlewareData("user");
    $products = $user->getProducts();

    return $res->render("profile/products", [
      "username" => $user->getUsername(),
      "products" => $products
    ]);
  }
}
