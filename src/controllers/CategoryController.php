<?php

namespace TestApp\Controllers;

use TestApp\Models\Category;
use TestApp\Core\Controller;
use TestApp\Core\Request;
use TestApp\Core\Response;

class CategoryController extends Controller
{
  public static function all(Request $req, Response $res)
  {
    $categories = Category::findAll();
    $res->render("categories/all", [
      "categories" => $categories
    ]);
  }
}
