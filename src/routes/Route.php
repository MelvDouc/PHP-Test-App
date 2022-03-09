<?php

namespace TestApp\Routes;

class Route
{
  public readonly string $name;
  private readonly string $path;
  private array $actions = [];

  public function __construct(string $name, string $path)
  {
    $this->name = $name;
    $this->path = $path;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function get(callable ...$actions)
  {
    $this->actions["GET"] = $actions;
    return $this;
  }

  public function post(callable ...$actions)
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

  public function middleware(callable ...$middleware): Route
  {
    foreach (array_keys($this->actions) as $method)
      $this->actions[$method] = [...$middleware, $this->actions[$method]];

    return $this;
  }
}
