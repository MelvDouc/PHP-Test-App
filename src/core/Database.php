<?php

namespace TestApp\Core;

use PDO;
use PDOStatement;
use TestApp\Models\User;

class Database
{
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

  public function getOne(string $tableName, array $search = [])
  {
    $whereClause = ($search)
      ? $this->getPlaceholders($search, " AND ")
      : "1";
    $statement = $this->prepare("SELECT * FROM $tableName WHERE $whereClause");
    $this->bindValues($statement, array_values($search));

    $statement->execute();
    return $statement->fetch();
  }

  public function getAll(string $tableName, string $column = "*"): array
  {
    $rows = $this
      ->query("SELECT $column FROM $tableName")
      ->fetchAll(PDO::FETCH_COLUMN);
    return (!$rows) ? [] : $rows;
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
