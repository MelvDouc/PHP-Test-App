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

    $sessionUser = $res->session->getUser();
    $username = $user->getUsername();
    $isUserProfile = is_array($sessionUser) && $sessionUser["id"] === $user->getId();
    $isAdmin = is_array($sessionUser) && $sessionUser["role"] === User::$ROLES["ADMIN"];

    $res->render("profile/index", compact("username", "isUserProfile", "isAdmin"));
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
