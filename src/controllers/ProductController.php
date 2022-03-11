<?php

namespace TestApp\Controllers;

use TestApp\Core\Application;
use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\Category;
use TestApp\Models\Product;
use TestApp\Models\User;
use TestApp\Services\ImageService;

class ProductController
{
  // Middleware
  public static function setProduct(Request $req, Response $res)
  {
    $product = Product::findOne([
      "slug" => $req->getParam("slug")
    ]);
    if (!$product)
      return $res->redirectNotFound();

    $req->setMiddlewareData("product", $product);
  }

  private static function canProductBeUpdatedByAppUser(Response $res, Product $product): bool
  {
    $user = $res->session->getUser();
    return (bool) $user &&
      ($user["id"] === $product->getSellerId() || $user["role"] === User::$ROLES["ADMIN"]);
  }

  public static function single(Request $req, Response $res)
  {
    $product = $req->getMiddlewareData("product");
    $sellerUsername = $product->getSeller()->getUsername();
    $res->render("products/single", compact("product", "sellerUsername"));
  }

  public static function add_GET(Request $req, Response $res)
  {
    $res->render("products/add", [
      "categories" => Category::findAll(["id", "name"])
    ]);
  }

  public static function add_POST(Request $req, Response $res)
  {
    $product = new Product($req->getBody());
    $image = $_FILES["image"] ?? null;

    $errors = array_merge($product->getErrors(), ImageService::check($image));

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
      $res->session->setErrorMessages("L'article n'a pas pu être ajouté.");
      Application::logErrors($e->getMessage());
      return $res->redirect("add-product");
    }
  }

  public static function update_GET(Request $req, Response $res)
  {
    $product = $req->getMiddlewareData("product");

    if (!self::canProductBeUpdatedByAppUser($res, $product))
      return $res->setForbidden();

    $res->session->setTempData("product-id", $product->getId());
    return $res->render("products/update", [
      "product" => $product,
      "categories" => Category::findAll(["id", "name"])
    ]);
  }

  public static function update_POST(Request $req, Response $res)
  {
    $product = Product::findOne(["id" => $res->session->getTempData("product-id")]);

    if (!self::canProductBeUpdatedByAppUser($res, $product))
      return $res->setForbidden();

    $product
      ->setName($req->getBody("name", ""))
      ->setDescription($req->getBody("description", ""))
      ->setPrice($req->getBody("price", 1))
      ->setQuantity($req->getBody("quantity", 0))
      ->setCategoryId($req->getBody("category_id", $product->getCategoryId()));

    $useDefaultImage = $req->getBody("use-default-image", false);

    if (!$useDefaultImage)
      $image = $_FILES["image"] ?? null;

    $errors = $product->getErrors();
    if (!$useDefaultImage)
      array_push($errors, ...ImageService::check($image));

    if ($errors) {
      $res->session->setErrorMessages($errors);
      $res->session->setFormData([
        "name" => $product->getName(),
        "description" => $product->getDescription(),
        "price" => $product->getPrice(),
        "quantity" => $product->getQuantity(),
        "category_id" => $product->getCategoryId()
      ]);
      return $res->redirect("update-product", ["slug" => $product->getSlug()]);
    }

    if ($useDefaultImage)
      $product->setDefaultImage();
    else
      $product->addImage($image);

    $product->update();
    $res->session->setSuccessMessage("L'article a bien été modifié.");
    return $res->redirect("product", ["slug" => $product->getSlug()]);
  }

  public static function delete(Request $req, Response $res)
  {
    $product = Product::findOne([
      "id" => (int)$req->getParam("id")
    ]);
    if (!$product)
      return $res->redirectNotFound();

    $name = $product->getName();

    try {
      $product->delete();
      $res->session->setSuccessMessage("L'article \"$name\" a bien été supprimé.");
    } catch (\Exception $e) {
      $res->session->setErrorMessages("L'article \"$name\" n'a pas pu être supprimé.");
      Application::logErrors($e->getMessage());
    } finally {
      return $res->redirect("admin-products-list");
    }
  }
}
