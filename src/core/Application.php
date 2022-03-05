<?php

namespace TestApp\Core;

class Application
{
  public static string $ROOT_DIR;
  public static Application $instance;

  public static function getDb(): Database
  {
    return self::$instance->db;
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

  /**
   * Find the registered path and method matching the current URL. Run the corresponding controller method if found, else set 404 response.
   */
  public function run(): void
  {
    $req = new Request();
    $res = new Response();
    $staticPath = $req->getPath();
    $httpMethod = $req->getMethod();

    foreach ($this->routes as $route) {
      $dynamicPath = $route["path"];

      if (!Path::compare($dynamicPath, $staticPath))
        continue;

      if (!array_key_exists($httpMethod, $route["methods"])) {
        $res->setMethodNotAllowed();
        return;
      }

      $params = Path::getParamsMap($dynamicPath, $staticPath);
      $req->setParams($params);

      call_user_func($route["methods"][$httpMethod], $req, $res);
      return;
    }

    // If required path wasn't found
    $res->redirectNotFound();
  }
}
