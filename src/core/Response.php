<?php

namespace TestApp\Core;

class Response
{
  public readonly Session $session;

  public function __construct()
  {
    $this->session = new Session();
  }

  private function getAppGlobals(): array
  {
    return [
      "user" => $this->session->getUser(),
      "successMessage" => $this
        ->session
        ->getSuccessMessage(),
      "errorMessages" => $this
        ->session
        ->getErrorMessages(),
      "formData" => $this->session->getFormData() ?? []
    ];
  }

  public function render(string $template, array $locals = []): void
  {
    $options = [];
    if ($_ENV["ENV"] === "production")
      $options["cache"] = Application::joinPaths("compilation-cache");
    $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(Application::joinPaths("views")), $options);
    $twig->addFunction(new \Twig\TwigFunction("route", function ($routeName, $context = []) {
      return Application::$instance->getFullUrl($routeName, $context);
    }));
    http_response_code(200);
    $locals["app"]  = $this->getAppGlobals();
    $twig->display("$template.twig", $locals);
  }

  public function redirect(string $routeName, array $context = []): void
  {
    $path = Application::$instance->getFullUrl($routeName, $context);
    header("Location: $path");
  }

  public function redirectNotFound(): void
  {
    http_response_code(404);
    exit("Page not found.");
  }

  public function setMethodNotAllowed(): void
  {
    http_response_code(405);
    exit("Method not allowed.");
  }

  public function setForbidden(): void
  {
    http_response_code(403);
    exit("Forbidden.");
  }
}
