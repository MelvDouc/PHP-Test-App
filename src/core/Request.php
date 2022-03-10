<?php

namespace TestApp\Core;

class Request
{
  private array $query;
  private array $body;
  private array $middlewareData = [];
  private array $params = [];

  public function __construct()
  {
    $this->query = $_GET;
    $this->body = array_map("trim", $_POST);
  }

  public function getMethod(): string
  {
    return strtoupper($_SERVER["REQUEST_METHOD"]);
  }

  public function getPath(): string
  {
    return $_SERVER["PATH_INFO"] ?? $_SERVER["REQUEST_URI"];
  }

  public function getQuery(): array
  {
    return $this->query;
  }

  public function getParam(string $key): ?string
  {
    return $this->params[$key] ?? null;
  }

  public function getParams(): array
  {
    return $this->params;
  }

  public function setParam(string $key, mixed $value): Request
  {
    $this->params[$key] = $value;
    return $this;
  }

  public function setParams(mixed $value): Request
  {
    $this->params = $value;
    return $this;
  }

  public function getMiddlewareData(string $key): mixed
  {
    return $this->middlewareData[$key] ?? null;
  }

  public function setMiddlewareData(string $key, mixed $value): Request
  {
    $this->middlewareData[$key] = $value;
    return $this;
  }

  public function getBody(string $key = null, mixed $default = null): mixed
  {
    if (!$key)
      return $this->body;

    if (!isset($this->body[$key]))
      return $default;

    $value = $this->body[$key];

    switch (gettype($default)) {
      case "int":
        return (int)$value;
      case "float":
        return (float) $value;
      case "bool":
        return (bool) $value;
      case "string":
      default:
        return $value;
    }
  }
}
