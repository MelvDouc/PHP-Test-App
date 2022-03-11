<?php

namespace TestApp\Routes;

class Route
{
  private readonly string $path;
  private array $actions = [];
  private array $middleware = [];

  public function __construct(string $path)
  {
    $this->path = $path;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function get(string|callable ...$actions)
  {
    $this->actions["GET"] = $actions;
    return $this;
  }

  public function post(string|callable ...$actions)
  {
    $this->actions["POST"] = $actions;
    return $this;
  }

  public function hasMethod(string $method): bool
  {
    return array_key_exists($method, $this->actions);
  }

  public function getAction(string $method)
  {
    return $this->actions[$method];
  }

  public function getMiddleware(): array
  {
    return $this->middleware;
  }

  public function addMiddleware(callable ...$middleware): Route
  {
    array_push($this->middleware, ...$middleware);

    return $this;
  }
}
