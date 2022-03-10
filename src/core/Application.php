<?php

namespace TestApp\Core;

use Exception;
use TestApp\Utils\Path;

class Application
{
  private static string $ROOT_DIR;
  public static Application $instance;

  /**
   * joinPaths("views, index.twig") => "[root]/views/index.twig"
   */
  public static function joinPaths(...$pathSegments): string
  {
    return realpath(implode("/", [self::$ROOT_DIR, ...$pathSegments]));
  }

  public static function logErrors(string ...$errors): void
  {
    error_log(implode(PHP_EOL, $errors), 3, self::joinPaths("data", "log", "php.log"));
  }

  private readonly Database $db;
  private readonly string $baseUrl;
  public array $routes;

  public function __construct(string $ROOT_DIR)
  {
    self::$ROOT_DIR = $ROOT_DIR;
    self::$instance = $this;
    $this->db = new Database();
    $this->baseUrl = ($_ENV["ENV"] === "development") ? "http://localhost:5000" : "";
    $this->routes = [];
  }

  public function getDb(): Database
  {
    return $this->db;
  }

  /**
   * @param string $routeName - Matches the first argument of `(new Router())->addRoute()`.
   * @param array $context - An optional associative array containing the placeholders in the route name as keys and their corresponding values. 
   */
  public function getFullUrl(string $routeName, array $context = []): string
  {
    if (!array_key_exists($routeName, $this->routes))
      throw new Exception("Invalid route name: $routeName.");

    return $this->baseUrl . "/" . Path::addContext(
      $this->routes[$routeName]->getPath(),
      $context
    );
  }

  /**
   * Add all of a router's routes to this instance's routes.
   */
  public function useRouter(Router $router): Application
  {
    foreach ($router->getRoutes() as $routeName => $route)
      $this->routes[$routeName] = $route;
    return $this;
  }

  public function useRouters(Router ...$routers): Application
  {
    foreach ($routers as $router)
      $this->useRouter($router);

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
      $dynamicPath = $route->getPath();

      if (!Path::compare($dynamicPath, $staticPath))
        continue;

      if (!$route->hasMethod($httpMethod)) {
        $res->setMethodNotAllowed();
        return;
      }

      $req->setParams(Path::getParamsMap($dynamicPath, $staticPath));

      foreach ($route->getMiddleware() as $middleware)
        call_user_func($middleware, $req, $res);

      foreach ($route->getAction($httpMethod) as $action)
        call_user_func($action, $req, $res);

      return;
    }

    // If required path wasn't found
    $res->redirectNotFound();
  }
}
