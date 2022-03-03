<?php

namespace TestApp\Controllers;

use TestApp\Core\Controller;
use TestApp\Core\Response;

class HomeController extends Controller
{
  public static function index($req, Response $res)
  {
    $res->render("home");
  }

  public static function about($req, Response $res)
  {
    $res->render("about");
  }
}
