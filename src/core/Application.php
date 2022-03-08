<?php

namespace TestApp\Core;

use Exception;
use TestApp\Utils\Path;

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
    if (!array_key_exists($routeName, self::$instance->routes))
      throw new Exception("Invalid route name.");

    return self::$instance->baseUrl . "/" . Path::addContext(
      self::$instance->routes[$routeName]["path"],
      $context
    );
  }

  public static function logError(\Exception $error): void
  {
    error_log($error->getMessage(), 3, self::joinPaths("data", "log", "php.log"));
  }

  private readonly Database $db;
  private readonly string $baseUrl;
  private array $routes;

  public function __construct(string $ROOT_DIR)
  {
    self::$ROOT_DIR = $ROOT_DIR;
    self::$instance = $this;
    $this->db = new Database();
    $this->baseUrl = ($_ENV["ENV"] === "development") ? "http://localhost:5000" : "";
    $this->routes = [];
  }

  /**
   * Add all of a router's routes to this instance's routes.
   */
  public function useRouter(Router $router): Application
  {
    foreach ($router->getRoutes() as $key => $value)
      $this->routes[$key] = $value;
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
      $actions = $route["methods"][$httpMethod];
      if (!is_callable($actions[0])) {
        call_user_func($actions, $req, $res);
        return;
      }
      foreach ($actions as $action)
        call_user_func($action, $req, $res);
      return;
    }

    // If required path wasn't found
    $res->redirectNotFound();
  }
}
