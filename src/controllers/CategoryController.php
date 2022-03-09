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
    $res->render("categories/all", [
      "categories" =>  Category::findAll()
    ]);
  }

  public static function single(Request $req, Response $res)
  {
    $category = Category::findOne([
      "name" => $req->getParam("category")
    ]);

    if (!$category)
      return $res->redirectNotFound();

    $res->render("categories/single", [
      "category" => $category,
      "products" => $category->getProducts()
    ]);
  }
}
