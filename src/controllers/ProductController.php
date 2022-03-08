<?php

namespace TestApp\Controllers;

use TestApp\Core\Application;
use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\Category;
use TestApp\Models\Product;
use TestApp\Models\User;
use TestApp\Utils\ImageValidator;

class ProductController
{
  // Middleware
  public static function setProduct(Request $req, Response $res)
  {
    $slug = $req->getParam("slug");
    if (!$slug)
      return $res->redirectNotFound();

    $product = Product::findOne(["slug" => $slug]);
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

  public static function update_GET(Request $req, Response $res)
  {
    $product = $req->getMiddlewareData("product");
    $user = $res->session->getUser();

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
    $newImage = (isset($_FILES["image"]) && isset($_FILES["image"]["error"]) && $_FILES["image"]["error"] === 0)
      ? $_FILES["image"]
      : null;

    $errors = $product->getErrors();
    if ($newImage)
      array_push($errors, ...ImageValidator::check($newImage));

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

    if ($newImage)
      $product->addImage($newImage);

    $product->update();
    $res->session->setSuccessMessage("L'article a bien été modifié.");
    return $res->redirect("product", ["slug" => $product->getSlug()]);
  }

  public static function delete(Request $req, Response $res)
  {
    $id = (int) $req->getParam("id");
    if (!$id)
      return $res->redirectNotFound();

    $product = Product::findOne(["id" => $id]);
    if (!$product)
      return $res->redirectNotFound();

    $name = $product->getName();

    try {
      $product->delete();
      $res->session->setSuccessMessage("L'article \"$name\" a bien été supprimé.");
    } catch (\Exception $e) {
      $res->session->setErrorMessages("L'article \"$name\" n'a pas pu être supprimé.");
      Application::logError($e);
    } finally {
      return $res->redirect("admin-products-list");
    }
  }
}
