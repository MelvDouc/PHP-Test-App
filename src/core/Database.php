<?php

namespace TestApp\Core;

use PDO;
use PDOStatement;

class Database
{
  public static array $comparers = [">", ">=", "<", "<=", "LIKE"];
  public static array $logicalOperators = ["AND", "OR"];

  public static function parseWhereClause(array $filter, string $comparer = "=", string $logicalOperator = null): array
  {
    if (!$filter)
      return [
        "placeholders" => "1",
        "values" => []
      ];

    $placeholders = [];
    $values = [];

    foreach ($filter as $key => $value) {
      if (in_array($key, self::$comparers)) {
        $result = self::parseWhereClause($value, $key, $logicalOperator);
        array_push($placeholders, $result["placeholders"]);
        array_push($values, ...$result["values"]);
        continue;
      }

      if (in_array($key, self::$logicalOperators)) {
        $result = self::parseWhereClause($value, $comparer, $key);
        array_push($placeholders, $result["placeholders"]);
        array_push($values, ...$result["values"]);
        continue;
      }

      array_push($placeholders, "$key $comparer ?");
      array_push($values, $value);
    }

    $placeholders = implode(($logicalOperator ? " $logicalOperator " : ""), $placeholders);
    return compact("placeholders", "values");
  }

  public \PDO $db;

  public function __construct()
  {
    $this->connect();
  }

  private function query(string $sql): PDOStatement|false
  {
    return $this->db->query($sql);
  }

  private function prepare(string $sql): PDOStatement|false
  {
    return $this->db->prepare($sql);
  }

  private function bindValues(PDOStatement &$statement, array $values)
  {
    for ($i = 1; $i <= count($values); $i++)
      $statement->bindValue($i, $values[$i - 1]);
  }

  private function getPlaceholders(array $arr, string $connector = ", "): string
  {
    return implode($connector, array_map(
      fn ($key) => "$key = ?",
      array_keys($arr)
    ));
  }

  public function connect(): void
  {
    try {
      $dotenv = \Dotenv\Dotenv::createImmutable(Application::$ROOT_DIR);
      $dotenv->load();
      $host = $_ENV["DB_HOST"];
      $dbName = $_ENV["DB_NAME"];

      $db = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8",
        $_ENV["DB_USER"],
        $_ENV["DB_PASSWORD"]
      );
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->db = $db;
    } catch (\PDOException $e) {
      echo $e->getMessage();
    }
  }

  public function getOne(string $tableName, array $filter)
  {
    $whereClause = self::parseWhereClause($filter);
    $placeholders = $whereClause["placeholders"];
    $statement = $this->prepare("SELECT * FROM $tableName WHERE $placeholders");
    $this->bindValues($statement, $whereClause["values"]);

    $statement->execute();
    return $statement->fetch();
  }

  public function getAll(string $tableName, array $filter = []): array
  {
    $whereClause = self::parseWhereClause($filter);
    $placeholders = $whereClause["placeholders"];
    $statement = $this->prepare("SELECT * FROM $tableName WHERE $placeholders");
    $this->bindValues($statement, $whereClause["values"]);

    $statement->execute();
    return $statement->fetchAll();
  }

  public function insert(string $tableName, array $keyValuePairs): bool
  {
    $columns = implode(", ", array_keys($keyValuePairs));
    $placeholders = implode(", ", array_fill(0, count($keyValuePairs), "?"));
    $statement = $this->prepare(
      "INSERT INTO $tableName ($columns) VALUES ($placeholders)"
    );
    $this->bindValues($statement, array_values($keyValuePairs));
    return $statement->execute();
  }

  public function update(string $tableName, array $updates, int $id): bool
  {
    $columns = $this->getPlaceholders($updates);
    $statement = $this->prepare("UPDATE $tableName SET $columns WHERE id = $id");
    $this->bindValues($statement, array_values($updates));

    return $statement->execute();
  }
}
