<?php

namespace TestApp\Core;

use TestApp\Routes\Route;

class Router
{
  private readonly string $basePath;
  private array $middleware = [];
  private array $routes = [];

  public function __construct(string $basePath)
  {
    $this->basePath = $basePath;
  }

  public function route(string $name, string $path): Route
  {
    $path = $this->prefix($path);
    $route = new Route($name, $path);
    $route->middleware(...$this->middleware);
    $this->routes[$name] = $route;
    return $this->routes[$name];
  }

  private function prefix(string $path): string
  {
    if ($this->basePath === "/")
      return $path;
    if ($path === "/")
      return $this->basePath;
    return $this->basePath . $path;
  }

  public function getRoutes(): array
  {
    return $this->routes;
  }

  public function addMiddleware(callable ...$middleware): Router
  {
    array_push($this->middleware, ...$middleware);
    return $this;
  }
}
