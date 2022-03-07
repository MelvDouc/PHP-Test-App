<?php

namespace TestApp\Core;

use DateTime;
use ReflectionProperty;

class Model
{
  public const TABLE_NAME = "";

  public static function findOne(array $filter): static|null
  {
    $dbRow = Application::getDb()->getOne(static::TABLE_NAME, $filter);
    return ($dbRow) ? new static($dbRow) : null;
  }

  public static function findAll(array $columns = ["*"], array $filter = [], string $orderBy = "id"): array
  {
    return Application::getDb()->getAll(static::TABLE_NAME, $columns, $filter, $orderBy);
  }

  public static function findAllJoin(array $tablesAndColumns, array $joins): array
  {
    $tablesAndColumns[static::TABLE_NAME] = ["*"];
    return Application::getDb()->join($tablesAndColumns, static::TABLE_NAME, $joins);
  }

  protected int $id;
  protected string $image = "default.jpg";
  protected DateTime $created_at;

  public function __construct(array $arr = [])
  {
    $className = static::class;

    foreach ($arr as $key => $value) {
      if (!property_exists($className, $key))
        continue;

      if ($key === "created_at") {
        $this->setCreatedAt($value);
        continue;
      }

      $type = (new ReflectionProperty($className, $key))
        ->getType()
        ->getName();

      switch ($type) {
        case "string":
          if (is_string($value))
            $this->{$key} = (string)$value;
          break;
        case "int":
          if (is_numeric($value))
            $this->{$key} = (int)$value;
          break;
        case "float":
          if (is_numeric($value))
            $this->{$key} = (float)$value;
          break;
        case "bool":
          $this->{$key} = (bool)$value;
          break;
      }
    }
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function setId(int $value)
  {
    $this->id = $value;
    return $this;
  }

  public function getCreatedAt(): DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(string $dateString)
  {
    $this->created_at = new DateTime($dateString);
    return $this;
  }

  public function getImage(): string
  {
    return $this->image;
  }

  public function setImage(string $value)
  {
    $this->image = $value;
    return $this;
  }

  public function addImage(array $img): void
  {
    $pathInfo = pathinfo($img["name"]);
    $this->image = md5($pathInfo["filename"]) . "." . $pathInfo["extension"];
    move_uploaded_file(
      $img["tmp_name"],
      Application::joinPaths("static", "img", static::TABLE_NAME, $this->image)
    );
  }
}
