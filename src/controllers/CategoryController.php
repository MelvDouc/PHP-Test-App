<?php

namespace TestApp\Controllers;

use TestApp\Core\Controller;
use TestApp\Core\Request;
use TestApp\Core\Response;

class CategoryController extends Controller
{
  public static function all(Request $req, Response $res)
  {
    $res->render("categories/all");
  }
}
