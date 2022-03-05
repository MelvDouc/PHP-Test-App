<?php

namespace TestApp\Models;

use DateTime;
use TestApp\Core\Model;
use TestApp\Core\Application;

class Category extends Model
{
  public const TABLE_NAME = "category";

  public static function findOne(array $search)
  {
    $dbRow = Application::getDb()->getOne(self::TABLE_NAME, $search);
    if (!$dbRow)
      return null;
    $instance = new Category();
    $instance
      ->setId($dbRow["id"])
      ->setName($dbRow["name"])
      ->setDescription($dbRow["description"])
      ->setImage($dbRow["image"])
      ->setCreatedAt(new DateTime($dbRow["created_at"]));

    return $instance;
  }

  public static function findAll(array $columns = ["*"], array $filter = []): array
  {
    return Application::getDb()->getAll(self::TABLE_NAME, $columns, $filter);
  }

  private string $name;
  private string $description;
  private string $image = "default.jpg";

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $value): Category
  {
    $this->name = $value;
    return $this;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $value): Category
  {
    $this->description = $value;
    return $this;
  }

  public function getImage(): string
  {
    return $this->image;
  }

  public function setImage(string $value): Category
  {
    $this->image = $value;
    return $this;
  }

  public function getProducts(): array
  {
    return Product::findAll(["*"], ["category_id" => $this->getId()]);
  }
}
