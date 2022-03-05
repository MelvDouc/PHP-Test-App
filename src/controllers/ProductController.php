<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\Product;

class ProductController
{
  public static function single(Request $req, Response $res)
  {
    $slug = $req->getParam("slug");

    if (!$slug)
      exit("Not found.");

    $product = Product::findOne(["slug" => $slug]);

    if (!$product)
      exit("Not found (2).");

    $sellerUsername = $product->getSeller()->getUsername();
    $res->render("products/single", compact("product", "sellerUsername"));
  }
}
