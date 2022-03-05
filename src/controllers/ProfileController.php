<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class ProfileController
{
  public static function index(Request $req, Response $res)
  {
    $user = self::getUser($req);

    if (!$user)
      return $res->redirectNotFound();

    $res->render("profile/index", [
      "username" => $user->getUsername()
    ]);
  }

  public static function products(Request $req, Response $res)
  {
    $user = self::getUser($req);

    if (!$user)
      return $res->redirectNotFound();

    $products = $user->getProducts();
    return $res->render("profile/products", [
      "username" => $user->getUsername(),
      "products" => $products
    ]);
  }

  private static function getUser(Request $req): User|null
  {
    $username = $req->getParam("username");
    if (!$username)
      return null;

    return User::findOne(["username" => $username]);
  }
}
