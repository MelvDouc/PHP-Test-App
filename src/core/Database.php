<?php

namespace TestApp\Core;

use PDO;
use PDOStatement;

class Database
{
  private const PLACEHOLDERS_KEY = "placeholders";
  private const VALUES_KEY = "values";
  private static array $comparers = [">", ">=", "<", "<=", "LIKE"];

  private static function commaJoin(array $arr): string
  {
    return implode(", ", $arr);
  }

  private static function escapeQuery(array $filter, string $comparer = "=", string $logicalOperator = null): array
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
        $result = self::escapeQuery($value, $key, $logicalOperator);
        array_push($placeholders, $result[self::PLACEHOLDERS_KEY]);
        array_push($values, ...$result[self::VALUES_KEY]);
        continue;
      }

      if ($key === "AND" || $key === "OR") {
        $result = self::escapeQuery($value, $comparer, $key);
        array_push($placeholders, $result[self::PLACEHOLDERS_KEY]);
        array_push($values, ...$result[self::VALUES_KEY]);
        continue;
      }

      array_push($placeholders, "$key $comparer ?");
      array_push($values, $value);
    }

    $placeholders = implode(($logicalOperator ? " $logicalOperator " : ""), $placeholders);
    return compact("placeholders", "values");
  }


  private static function bindValues(PDOStatement &$statement, array $values): void
  {
    for ($i = 1; $i <= count($values); $i++)
      $statement->bindValue($i, $values[$i - 1]);
  }

  public \PDO $db;

  public function __construct()
  {
    $this->connect();
  }

  private function connect(): void
  {
    try {
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

  private function query(string $sql): PDOStatement|false
  {
    return $this->db->query($sql);
  }

  private function prepare(string $sql): PDOStatement|false
  {
    return $this->db->prepare($sql);
  }

  public function getOne(string $tableName, array $filter)
  {
    $whereClause = self::escapeQuery($filter);
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $statement = $this->prepare("SELECT * FROM $tableName WHERE $placeholders");
    self::bindValues($statement, $whereClause[self::VALUES_KEY]);

    $statement->execute();
    return $statement->fetch();
  }

  public function getAll(string $tableName, array $columns = ["*"], array $filter = []): array
  {
    $whereClause = self::escapeQuery($filter);
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $columns = self::commaJoin($columns);
    $statement = $this->prepare("SELECT $columns FROM $tableName WHERE $placeholders");
    self::bindValues($statement, $whereClause[self::VALUES_KEY]);

    $statement->execute();
    return $statement->fetchAll();
  }

  public function join(array $tablesAndColumns, string $mainTable, array $joins): array
  {
    $columns = [];

    foreach ($tablesAndColumns as $tableName => $colArray)
      foreach ($colArray as $columnName)
        $columns[] = $tableName . "." . $columnName;

    $columns = self::commaJoin($columns);

    foreach ($joins as $tableName => $association) {
      $primaryKey = array_keys($association)[0];
      $foreignKey = array_values($association)[0];
      $joins[$tableName] = "JOIN $tableName ON $tableName.$primaryKey = $mainTable.$foreignKey";
    }

    $joins = implode(" ", $joins);

    return $this
      ->query("SELECT $columns FROM $mainTable $joins")
      ->fetchAll();
  }

  public function insert(string $tableName, array $keyValuePairs): bool
  {
    $columns = self::commaJoin(array_keys($keyValuePairs));
    $placeholders = self::commaJoin(array_fill(0, count($keyValuePairs), "?"));

    $statement = $this->prepare(
      "INSERT INTO $tableName ($columns) VALUES ($placeholders)"
    );
    self::bindValues($statement, array_values($keyValuePairs));
    return $statement->execute();
  }

  public function update(string $tableName, array $updates, array $filter): bool
  {
    $setClause = self::escapeQuery($updates, "=", ",");
    $whereClause = self::escapeQuery($filter);
    $columns = $setClause[self::PLACEHOLDERS_KEY];
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];

    $statement = $this->prepare("UPDATE $tableName SET $columns WHERE $placeholders");
    self::bindValues(
      $statement,
      array_merge($setClause[self::VALUES_KEY], $whereClause[self::VALUES_KEY])
    );
    return $statement->execute();
  }

  public function delete(string $tableName, array $filter): bool
  {
    $whereClause = self::escapeQuery($filter);
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $statement = $this->prepare("DELETE FROM $tableName WHERE $placeholders");
    self::bindValues($statement, $whereClause[self::VALUES_KEY]);
    return $statement->execute();
  }
}
