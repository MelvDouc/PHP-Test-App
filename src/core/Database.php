<?php

namespace TestApp\Core;

use PDO;
use PDOStatement;
use TestApp\Exceptions\DatabaseException;

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
      $result = null;
      if (in_array($key, self::$comparers))
        $result = self::escapeQuery($value, $key, $logicalOperator);
      else if ($key === "AND" || $key === "OR")
        $result = self::escapeQuery($value, $comparer, $key);

      if ($result) {
        array_push($placeholders, $result[self::PLACEHOLDERS_KEY]);
        array_push($values, ...$result[self::VALUES_KEY]);
      } else {
        array_push($placeholders, "$key $comparer ?");
        array_push($values, $value);
      }
    }

    $placeholders = implode(($logicalOperator ? " $logicalOperator " : ""), $placeholders);
    return compact("placeholders", "values");
  }

  private static function bindValues(PDOStatement &$statement, array $values): void
  {
    foreach ($values as $i => $value)
      $statement->bindValue($i + 1, $value);
  }

  private readonly PDO $conn;

  public function __construct()
  {
    $this->conn = $this->getConnection();
  }

  private function getConnection(): PDO
  {
    try {
      $host = $_ENV["DB_HOST"];
      $dbName = $_ENV["DB_NAME"];

      $conn = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8",
        $_ENV["DB_USER"],
        $_ENV["DB_PASSWORD"]
      );
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $conn;
    } catch (\PDOException $e) {
      echo $e->getMessage();
    }
  }

  private function query(string $sql): PDOStatement
  {
    $query = $this->conn->query($sql);
    if (!$query)
      throw new DatabaseException("Query failed.", $sql);
    return $query;
  }

  private function prepare(string $sql): PDOStatement
  {
    $statement = $this->conn->prepare($sql);
    if (!$statement)
      throw new DatabaseException("Statement preparation failed.", $sql);
    return $statement;
  }

  public function getOne(string $tableName, array $filter)
  {
    $whereClause = self::escapeQuery($filter);
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $sql = "SELECT * FROM $tableName WHERE $placeholders";
    $statement = $this->prepare($sql);
    self::bindValues($statement, $whereClause[self::VALUES_KEY]);

    if (!$statement->execute())
      throw new DatabaseException("`getOne` failed", $sql);
    return $statement->fetch();
  }

  public function getAll(string $tableName, array $columns, array $filter, string $orderBy): array
  {
    $whereClause = self::escapeQuery($filter);
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $columns = self::commaJoin($columns);
    $sql = "SELECT $columns FROM $tableName WHERE $placeholders ORDER BY $orderBy";
    $statement = $this->prepare($sql);
    self::bindValues($statement, $whereClause[self::VALUES_KEY]);

    if (!$statement->execute())
      throw new DatabaseException("`getAll` failed", $sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
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
    $sql = "SELECT $columns FROM $mainTable $joins";
    $queryObj = $this->query($sql);

    if (!$queryObj)
      throw new DatabaseException("`join` failed", $sql);

    return $queryObj->fetchAll();
  }

  public function insert(string $tableName, array $insertions): bool
  {
    $columns = self::commaJoin(array_keys($insertions));
    $placeholders = self::commaJoin(array_fill(0, count($insertions), "?"));
    $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

    $statement = $this->prepare($sql);
    self::bindValues($statement, array_values($insertions));

    if (!$statement->execute())
      throw new DatabaseException("Insertion to $tableName failed", $sql);
    return true;
  }

  public function update(string $tableName, array $updates, array $filter): bool
  {
    $setClause = self::escapeQuery($updates, "=", ",");
    $whereClause = self::escapeQuery($filter);
    $columns = $setClause[self::PLACEHOLDERS_KEY];
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $sql = "UPDATE $tableName SET $columns WHERE $placeholders";

    $statement = $this->prepare($sql);
    self::bindValues(
      $statement,
      [...$setClause[self::VALUES_KEY], ...$whereClause[self::VALUES_KEY]]
    );

    if (!$statement->execute())
      throw new DatabaseException("Update on $tableName failed.", $sql);
    return true;
  }

  public function delete(string $tableName, array $filter): bool
  {
    $whereClause = self::escapeQuery($filter);
    $placeholders = $whereClause[self::PLACEHOLDERS_KEY];
    $sql = "DELETE FROM $tableName WHERE $placeholders";
    $statement = $this->prepare($sql);
    self::bindValues($statement, $whereClause[self::VALUES_KEY]);

    if (!$statement->execute())
      throw new DatabaseException("Deletion on $tableName failed.", $sql);
    return true;
  }
}
