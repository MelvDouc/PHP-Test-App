<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\Product;
use TestApp\Models\User;

class AdminController
{
  public static function home(Request $req, Response $res)
  {
    $res->render("admin/home");
  }

  public static function usersList(Request $req, Response $res)
  {
    $res->render("admin/users-list", [
      "users" => User::findAll()
    ]);
  }

  public static function productsList(Request $req, Response $res)
  {
    $res->render("admin/products-list", [
      "products" => Product::findAllJoin([
        "category" => ["name AS category"],
        "user" => ["username AS seller"]
      ], [
        "category" => ["id" => "category_id"],
        "user" => ["id" => "seller_id"]
      ])
    ]);
  }
}
