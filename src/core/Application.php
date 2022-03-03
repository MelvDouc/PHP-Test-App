<?php

namespace TestApp\Core;

class Application
{
  public static string $ROOT_DIR;
  public static Application $instance;

  public static function vardump(mixed $arg): void
  {
    echo "<pre>";
    var_dump($arg);
    echo "</pre>";
  }

  public static function getDb(): Database
  {
    return self::$instance->db;
  }

  private function doRoutesMatch(array $dynamicPathSegments, array $staticPathSegments): bool
  {
    if (count($dynamicPathSegments) !== count($staticPathSegments))
      return false;

    foreach ($dynamicPathSegments as $i => $segment) {
      if ($segment === $staticPathSegments[$i])
        continue;

      if (!str_contains($segment, ":"))
        return false;
    }

    return true;
  }

  public Database $db;
  private array $routes = [];

  public function __construct(string $ROOT_DIR)
  {
    self::$ROOT_DIR = $ROOT_DIR;
    self::$instance = $this;
    $this->db = new Database();
  }

  public function useRouter(Router $router): Application
  {
    $this->routes = array_merge($this->routes, $router->getRoutes());
    return $this;
  }

  public function getPathByName(string $name): string
  {
    return $this->routes[$name]["path"];
  }

  public function run(): void
  {
    $req = new Request();
    $res = new Response();
    $staticPathSegments = explode("/", $req->getPath());
    $httpMethod = $req->getMethod();

    foreach ($this->routes as $route) {
      $dynamicPathSegments = explode("/", $route["path"]);

      if (!$this->doRoutesMatch($dynamicPathSegments, $staticPathSegments))
        continue;

      if (!array_key_exists($httpMethod, $route["methods"])) {
        $res->setMethodNotAllowed();
        return;
      }

      foreach ($dynamicPathSegments as $i => $segment)
        if (str_contains($segment, ":"))
          $req->setParam(substr($segment, 1), $staticPathSegments[$i]);

      call_user_func($route["methods"][$httpMethod], $req, $res);
      return;
    }
  }
}
