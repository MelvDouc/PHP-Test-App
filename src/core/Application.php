<?php

namespace TestApp\Core;

class Application
{
  public static string $ROOT_DIR;
  public static Application $instance;

  /**
   * @return Database The database instance of the current application.
   */
  public static function getDb(): Database
  {
    return self::$instance->db;
  }

  /**
   * joinPaths("views, index.twig") => "[root]/views/index.twig"
   */
  public static function joinPaths(...$pathSegments): string
  {
    return implode("/", [self::$ROOT_DIR, ...$pathSegments]);
  }

  /**
   * @param string $routeName - Matches the first argument of `(new Router())->addRoute()`.
   * @param array $context - An optional associative array containing the placeholders in the route name as keys and their corresponding values. 
   */
  public static function getFullRoute(string $routeName, array $context = [])
  {
    return "http://localhost:5000/" . Path::addContext(
      self::$instance->routes[$routeName]["path"],
      $context
    );
  }

  public Database $db;
  private array $routes = [];

  public function __construct(string $ROOT_DIR)
  {
    self::$ROOT_DIR = $ROOT_DIR;
    self::$instance = $this;
    $this->db = new Database();
  }

  /**
   * Add all of a router's routes to this instance's routes.
   */
  public function useRouter(Router $router): Application
  {
    $this->routes = array_merge($this->routes, $router->getRoutes());
    return $this;
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

      $req->setParams(Path::getParamsMap($dynamicPath, $staticPath));
      call_user_func($route["methods"][$httpMethod], $req, $res);
      return;
    }

    // If required path wasn't found
    $res->redirectNotFound();
  }
}
