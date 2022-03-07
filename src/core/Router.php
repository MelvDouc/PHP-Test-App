<?php

namespace TestApp\Core;

class Router
{
  private readonly string $basePath;
  private readonly array $routes;

  public function __construct(string $basePath)
  {
    $this->basePath = $basePath;
    $this->routes = [];
  }

  private function prefix(string $path): string
  {
    if ($this->basePath === '/')
      return $path;
    if ($path === '/')
      return $this->basePath;
    return $this->basePath . $path;
  }

  public function getRoutes(): array
  {
    return $this->routes;
  }

  public function addRoute(string $name, array $params): Router
  {
    $this->routes[$name] = [
      "path" => $this->prefix($params["path"]),
      "methods" => $params["methods"]
    ];
    return $this;
  }
}
