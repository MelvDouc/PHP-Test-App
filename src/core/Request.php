<?php

namespace TestApp\Core;

class Request
{
  private array $params = [];

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

  public function getBody(): array
  {
    return array_map("trim", $_POST);
  }
}
