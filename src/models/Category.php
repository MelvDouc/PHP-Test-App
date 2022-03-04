<?php

namespace TestApp\Models;

use TestApp\Core\Model;
use TestApp\Core\Application;

class Category extends Model
{
  public static $TABLE_NAME = "category";

  public static function findOne(array $search)
  {
    $dbRow = Application::getDb()->getOne(self::$TABLE_NAME, $search);
    if (!$dbRow)
      return null;
    $instance = new Category();
    $instance
      ->setId($dbRow["id"])
      ->setName($dbRow["name"])
      ->setDescription($dbRow["description"])
      ->setImage($dbRow["image"])
      ->setCreatedAt($dbRow["created_at"]);

    return $instance;
  }

  public static function findAll(array $filter = []): array
  {
    return Application::getDb()->getAll(self::$TABLE_NAME, $filter);
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
}
