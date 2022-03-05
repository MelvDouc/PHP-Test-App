<?php

namespace TestApp\Models;

use DateTime;
use TestApp\Core\Application;
use TestApp\Core\Model;

class Product extends Model
{
  public const TABLE_NAME = "product";

  public static function findOne(array $search)
  {
    $dbRow = Application::getDb()->getOne(self::TABLE_NAME, $search);
    if (!$dbRow)
      return null;
    $instance = new Product();
    $instance
      ->setId($dbRow["id"])
      ->setName($dbRow["name"])
      ->setDescription($dbRow["description"])
      ->setPrice((float) $dbRow["price"])
      ->setQuantity((int) $dbRow["quantity"])
      ->setCategoryId((int) $dbRow["category_id"])
      ->setSellerId((int) $dbRow["seller_id"])
      ->setImage($dbRow["image"])
      ->setCreatedAt(new DateTime($dbRow["created_at"]));

    return $instance;
  }

  public static function findAll(array $filter = []): array
  {
    return Application::getDb()->getAll(self::TABLE_NAME, $filter);
  }

  private string $name;
  private string $description;
  private float $price;
  private int $quantity;
  private int $category_id;
  private int $seller_id;
  private string $image;

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): Product
  {
    $this->name = $name;
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

  public function getImage(): string
  {
    return $this->image;
  }

  public function setImage(string $image): Product
  {
    $this->image = $image;
    return $this;
  }
}
