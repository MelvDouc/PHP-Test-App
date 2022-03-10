<?php

namespace TestApp\Models;

use DateTime;
use TestApp\Core\Application;
use TestApp\Core\Model;

class Product extends Model
{
  public const TABLE_NAME = "product";

  protected string $name;
  protected string $slug;
  protected string $description;
  protected float $price;
  protected int $quantity;
  protected int $category_id;
  protected int $seller_id;

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): Product
  {
    $this->name = $name;
    return $this;
  }

  public function getSlug(): string
  {
    return $this->slug;
  }

  private function setSlug(): Product
  {
    $slug = preg_replace("/\s+/", "-", $this->name);

    if (self::findOne(["slug" => $slug])) {
      $timestamp = (new DateTime())->format("Y-m-d-H-i-s");
      $slug .= "-$timestamp";
    }

    $this->slug = $slug;
    return $this;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $description): Product
  {
    $this->description = $description;
    return $this;
  }

  public function getPrice(): float
  {
    return $this->price;
  }

  public function setPrice(float $price): Product
  {
    $this->price = $price;
    return $this;
  }

  public function getQuantity(): int
  {
    return $this->quantity;
  }

  public function setQuantity(int $quantity): Product
  {
    $this->quantity = $quantity;
    return $this;
  }

  public function getCategoryId(): int
  {
    return $this->category_id;
  }

  public function setCategoryId(int $category_id): Product
  {
    $this->category_id = $category_id;
    return $this;
  }

  public function getSellerId(): int
  {
    return $this->seller_id;
  }

  public function setSellerId($seller_id): Product
  {
    $this->seller_id = $seller_id;
    return $this;
  }

  public function getCategory(): Category
  {
    return Category::findOne(["id" => $this->category_id]);
  }

  public function getSeller(): User
  {
    return User::findOne(["id" => $this->seller_id]);
  }

  public function getErrors(): array
  {
    $errors = [];

    if (!isset($this->name) || $this->name === "")
      $errors[] = "L'article doit avoir un nom.";
    else if (strlen($this->name) < 5 || strlen($this->name) > 100)
      $errors[] = "Le nom de l'article doit contenir entre 5 et 100 caractères.";
    if (!isset($this->description) || $this->description === "")
      $errors[] = "L'article doit avoir une description.";
    if (!isset($this->price) || $this->price < 0)
      $errors[] = "Le prix doit être un nombre positif.";
    if (!isset($this->quantity) || $this->quantity < 1)
      $errors[] = "La quantité doit être supérieure ou égale à 1.";
    if (!isset($this->category_id) || !Category::findOne(["id" => $this->category_id]))
      $errors[] = "Catégorie non trouvée.";

    return $errors;
  }

  public function save()
  {
    if (!isset($this->slug))
      $this->setSlug();

    $insertion = Application::$instance->getDb()->insert(self::TABLE_NAME, [
      "name" => $this->name,
      "slug" => $this->slug,
      "description" => $this->description,
      "price" => $this->price,
      "quantity" => $this->quantity,
      "category_id" => $this->category_id,
      "seller_id" => $this->seller_id,
      "image" => $this->image
    ]);

    if (!$insertion)
      throw new \Exception("Product could not be saved.");
  }

  public function update()
  {
    return  Application::$instance->getDb()->update(self::TABLE_NAME, [
      "name" => $this->name,
      "description" => $this->description,
      "price" => $this->price,
      "quantity" => $this->quantity,
      "category_id" => $this->category_id,
      "seller_id" => $this->seller_id,
      "image" => $this->image
    ], [
      "id" => $this->id
    ]);
  }
}
