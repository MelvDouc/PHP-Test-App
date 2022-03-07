<?php

namespace TestApp\Core;

class Request
{
  private array $body;
  private array $params = [];
  private array $middlewareData = [];

  public function __construct()
  {
    $this->body = $_POST;
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
    return $_GET;
  }

  public function getParam(string $key): string|null
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
      return array_map("trim", $this->body);

    if (!isset($this->body[$key]))
      return $default;

    $value = trim($this->body[$key]);
    $type = gettype($default);
    if ($type === "string")
      return $value;
    if ($type === "int")
      return (int) $value;
    if ($type === "float")
      return (float) $value;
    if ($type === "bool")
      return (bool) $value;
    return $value;
  }
}
