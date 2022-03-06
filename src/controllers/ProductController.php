<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\Category;
use TestApp\Models\Product;
use TestApp\Utils\ImageValidator;

class ProductController
{
  public static function single(Request $req, Response $res)
  {
    $slug = $req->getParam("slug");

    if (!$slug)
      return $res->redirectNotFound();

    $product = Product::findOne(["slug" => $slug]);

    if (!$product)
      return $res->redirectNotFound();

    $sellerUsername = $product->getSeller()->getUsername();
    $res->render("products/single", compact("product", "sellerUsername"));
  }

  public static function add_GET(Request $req, Response $res)
  {
    $user = $res->session->getUser();

    if (!$user) {
      $res->session->setErrorMessages(["Vous n'êtes pas connecté(e)."]);
      return $res->redirect("sign-in");
    }

    $res->render("products/add", [
      "categories" => Category::findAll(["id", "name"])
    ]);
  }

  public static function add_POST(Request $req, Response $res)
  {

    $product = new Product($req->getBody());
    $image = $_FILES["image"] ?? null;

    $errors = array_merge($product->getErrors(), ImageValidator::check($image));

    if ($errors) {
      $res->session->setErrorMessages($errors);
      $res->session->setFormData([
        "name" => $product->getName(),
        "description" => $product->getDescription(),
        "price" => $product->getPrice(),
        "quantity" => $product->getQuantity(),
        "category_id" => $product->getCategoryId()
      ]);
      return $res->redirect("add-product");
    }

    $product
      ->setSellerId((int) $res->session->getUser()["id"])
      ->addImage($image);

    try {
      $product->save();
      $res->session->setSuccessMessage("L'article a bien été ajouté.");
      return $res->redirect("product", ["slug" => $product->getSlug()]);
    } catch (\Exception $e) {
      $res->session->setErrorMessages([$e]);
      return $res->redirect("add-product");
    }
  }
}
