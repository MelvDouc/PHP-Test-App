<?php

namespace TestApp\Controllers;

use TestApp\Core\Application;
use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class ProfileController
{
  public static function index(Request $req, Response $res)
  {
    $username = $req->getParams()["username"] ?? null;

    if (!$username)
      exit("No username.");

    $user = User::findOne(["username" => $username]);

    if (!$user)
      exit("No user.");

    $res->render("profile/index", [
      "username" => $user->getUsername()
    ]);
  }
}
