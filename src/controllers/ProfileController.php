<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class ProfileController
{
  public static function index(Request $req, Response $res)
  {
    $username = $req->getParam("username");
    if (!$username || !($user = User::findOne(["username" => $username])))
      return $res->redirectNotFound();

    if (!$user) {
      echo "<pre>";
      var_dump($req);
      echo "</pre>";
      exit;
    }

    $res->render("profile/index", [
      "username" => $user->getUsername()
    ]);
  }

  public static function products(Request $req, Response $res)
  {
    $username = $req->getParam("username");
    if (!$username || !($user = User::findOne(["username" => $username])))
      return $res->redirectNotFound();
    $products = $user->getProducts();

    return $res->render("profile/products", [
      "username" => $user->getUsername(),
      "products" => $products
    ]);
  }
}
