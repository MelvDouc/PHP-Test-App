<?php

namespace TestApp\Models;

use TestApp\Core\Model;

class Category extends Model
{
  public const TABLE_NAME = "category";

  protected string $name;
  protected string $description;
  protected string $image = "default.jpg";

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
    return Product::findAll(["*"], ["category_id" => $this->id]);
  }
}
