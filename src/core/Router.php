<?php

namespace TestApp\Core;

class Router
{
  private readonly string $basePath;
  private array $middleware;
  private array $routes;

  public function __construct(string $basePath)
  {
    $this->basePath = $basePath;
    $this->middleware = [];
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
    $params["path"] = $this->prefix($params["path"]);

    if ($this->middleware)
      foreach ($params["methods"] as $key => $value)
        $params["methods"][$key] = [...$this->middleware, $value];
    $this->routes[$name] = $params;

    // $this->routes[$name] = [
    //   "path" => $this->prefix($params["path"]),
    //   "methods" => $params["methods"]
    // ];
    return $this;
  }

  public function addMiddleware(callable $middleware): Router
  {
    $this->middleware[] = $middleware;
    return $this;
  }
}
