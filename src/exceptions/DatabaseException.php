<?php

namespace TestApp\Core\Exceptions;

class DatabaseException extends \Exception
{
  private readonly string $sql;

  public function __construct(string $message, string $sql)
  {
    parent::__construct($message, 0, null);
    $this->sql = $sql;
  }

  public function getSql(): string
  {
    return $this->sql;
  }
}
