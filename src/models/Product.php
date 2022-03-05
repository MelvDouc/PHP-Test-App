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
    return new Product($dbRow);
  }

  public static function findAll(array $columns = ["*"], array $filter = []): array
  {
    return Application::getDb()->getAll(self::TABLE_NAME, $columns, $filter);
  }

  private string $name;
  private string $slug;
  private string $description;
  private float $price;
  private int $quantity;
  private int $category_id;
  private int $seller_id;
  private string $image;

  public function __construct(array $product = [])
  {
    extract($product);

    if (isset($id) && is_numeric($id))
      $this->id = $id;
    if (isset($name) && is_string($name))
      $this->name = $name;
    if (isset($slug) && is_string($slug))
      $this->slug = $slug;
    if (isset($description) && is_string($description))
      $this->description = $description;
    if (isset($price) && is_numeric($price))
      $this->price = (int) $price;
    if (isset($quantity) && is_numeric($quantity))
      $this->quantity = (int) $quantity;
    if (isset($category_id) && is_numeric($category_id))
      $this->category_id = (int) $category_id;
    if (isset($seller_id) && is_numeric($seller_id))
      $this->seller_id = (int) $seller_id;
    if (isset($image) && is_string($image))
      $this->image = $image;
    if (isset($created_at) && is_string($created_at))
      $this->setCreatedAt(new DateTime($created_at));
  }

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

  public function getImage(): string
  {
    return $this->image;
  }

  public function setImage(string $imageName): Product
  {
    $this->image = $imageName;
    return $this;
  }

  public function addImage(array $img): void
  {
    $pathInfo = pathinfo($img["name"]);
    $this->image = md5($pathInfo["filename"]) . "." . $pathInfo["extension"];
    move_uploaded_file(
      $img["tmp_name"],
      Application::joinPaths("static", "img", "products", $this->image)
    );
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

    $insertion = Application::getDb()->insert(self::TABLE_NAME, [
      "name" => $this->getName(),
      "slug" => $this->slug,
      "description" => $this->description,
      "price" => $this->price,
      "quantity" => $this->quantity,
      "category_id" => $this->category_id,
      "seller_id" => $this->seller_id,
      "image" => $this->image
    ]);

    if (!$insertion)
      throw new \Exception("Product could not be saved");
  }
}
