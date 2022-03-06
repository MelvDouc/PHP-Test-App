<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class AdminController
{
  public static function home(Request $req, Response $res)
  {
    $res->render("admin/home");
  }

  public static function usersList(Request $req, Response $res)
  {
    return $res->render("admin/users-list", [
      "users" => User::findAll()
    ]);
  }
}
