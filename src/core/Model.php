<?php

namespace TestApp\Core;

use DateTime;
use Exception;
use ReflectionProperty;
use TestApp\Exceptions\DatabaseException;

abstract class Model
{
  public const TABLE_NAME = "";
  private const DEFAULT_IMAGE = "default.jpg";

  public static function findOne(array $filter): ?static
  {
    try {
      $dbRow = Application::$instance->getDb()->getOne(static::TABLE_NAME, $filter);
      return ($dbRow) ? new static($dbRow) : null;
    } catch (DatabaseException $e) {
      Application::logErrors($e->getMessage(), $e->getSql());
      return null;
    }
  }

  public static function findAll(array $columns = ["*"], array $filter = [], string $orderBy = "id"): array
  {
    try {
      return Application::$instance
        ->getDb()
        ->getAll(static::TABLE_NAME, $columns, $filter, $orderBy);
    } catch (DatabaseException $e) {
      Application::logErrors($e->getMessage(), $e->getSql());
      return [];
    }
  }

  public static function findAllJoin(array $tablesAndColumns, array $joins): array
  {
    try {
      $tablesAndColumns[static::TABLE_NAME] = ["*"];
      return Application::$instance
        ->getDb()
        ->join($tablesAndColumns, static::TABLE_NAME, $joins);
    } catch (DatabaseException $e) {
      Application::logErrors($e->getMessage(), $e->getSql());
      return [];
    }
  }

  protected int $id;
  protected string $image = self::DEFAULT_IMAGE;
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

  public function setDefaultImage()
  {
    $this->image = self::DEFAULT_IMAGE;
    return $this;
  }

  public function addImage(array $img): void
  {
    $pathInfo = pathinfo($img["name"]);
    $this->image = md5($pathInfo["filename"]) . "." . $pathInfo["extension"];
    move_uploaded_file(
      $img["tmp_name"],
      Application::joinPaths("public", "img", static::TABLE_NAME, $this->image)
    );
  }

  public function delete(): void
  {
    $tableName = static::TABLE_NAME;
    if (!Application::$instance->getDb()->delete($tableName, ["id" => $this->id]))
      throw new Exception("[$tableName] couldn't be deleted.");
  }
}
