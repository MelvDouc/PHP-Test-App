<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class ProfileController
{
  public static function index(Request $req, Response $res)
  {
    $id = $req->getParams()["id"];

    if (!$id)
      exit("No id.");

    $user = User::findOne(["id" => $id]);

    if (!$user)
      exit("No user.");
    // $query = $req->getQuery();

    // if (!($username = $query["pseudo"]))
    //   exit("No username.");

    // if (!($user = User::findOne(["username" => $username])))
    //   exit("user not found");

    $res->render("profile/index", [
      "username" => $user->getUsername()
    ]);
  }
}
