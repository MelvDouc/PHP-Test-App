<?php

namespace TestApp\Core;

class Router
{
  private string $basePath;
  private array $routes = [];

  public function __construct(string $basePath)
  {
    $this->basePath = $basePath;
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

  public function setRoutes(array $routes): Router
  {
    $this->routes = array_map(function ($item) {
      $item['path'] = $this->prefix($item['path']);
      return $item;
    }, $routes);
    return $this;
  }
}
