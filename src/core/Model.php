<?php

namespace TestApp\Core;

use DateTime;
use ReflectionProperty;

class Model
{
  public const TABLE_NAME = "";

  public static function findOne(array $filter)
  {
    $dbRow = Application::getDb()->getOne(static::TABLE_NAME, $filter);
    if (!$dbRow)
      return null;
    return new static($dbRow);
  }

  public static function findAll(array $columns = ["*"], array $filter = []): array
  {
    return Application::getDb()->getAll(static::TABLE_NAME, $columns, $filter);
  }

  protected int $id;
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
}
