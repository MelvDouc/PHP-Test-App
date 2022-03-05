<?php

namespace TestApp\Controllers;

use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\Category;
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

  public static function add_GET(Request $req, Response $res)
  {
    $user = $res->session->getUser();

    if (!$user) {
      $res->session->setErrorMessages(["Vous n'êtes pas connecté(e)."]);
      return $res->redirect("sign-in");
    }

    $categories = Category::findAll(["id", "name"]);

    $res->render("products/add", [
      "categories" => $categories
    ]);
  }

  public static function add_POST(Request $req, Response $res)
  {

    $body = $req->getBody();
    $product = new Product($req->getBody());
    $image = $_FILES["image"] ?? null;

    $imageErrors = [];

    if (!$image || $image["error"] !== 0) {
      $imageErrors[] = "Fichier d'image manquant ou invalide.";
    } else {
      if ($image["size"] > 2e6)
        $imageErrors[] = "Le fichier ne doit pas dépasser 2 MO.";
      if (!in_array($image["type"], ["image/jpg", "image/jpeg", "image/png", "image/gif"]))
        $imageErrors[] = "Le fichier doit être une image.";
    }

    $errors = array_merge($product->getErrors(), $imageErrors);

    if ($errors) {
      $res->session->setErrorMessages($errors);
      $res->session->setFormData([
        "name" => $product->getName(),
        "description" => $product->getDescription(),
        "price" => $product->getPrice(),
        "quantity" => $product->getQuantity()
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
