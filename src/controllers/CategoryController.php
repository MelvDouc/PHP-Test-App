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

  public static function single(Request $req, Response $res)
  {
    $categoryName = $req->getParam("category");

    if (!$categoryName)
      exit("not found");

    $category = Category::findOne(["name" => $categoryName]);

    if (!$category)
      exit("not found (2)");

    $products = $category->getProducts();
    $res->render("categories/single", [
      "category" => $category,
      "products" => $products
    ]);
  }
}
