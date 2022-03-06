<?php

namespace TestApp\Core;

class Response
{
  public Session $session;

  public function __construct()
  {
    $this->session = new Session();
  }

  private function getApp(): array
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
    $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(Application::joinPaths("views")), [
      "cache" => Application::joinPaths("compilation-cache")
    ]);
    $twig->addFunction(new \Twig\TwigFunction("route", function ($routeName, $context = []) {
      return Application::getFullRoute($routeName, $context);
    }));
    $locals["app"]  = $this->getApp();
    http_response_code(200);
    $twig->display("$template.twig", $locals);
  }

  public function redirect(string $routeName, array $context = []): void
  {
    $path = Application::getFullRoute($routeName, $context);
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
}
