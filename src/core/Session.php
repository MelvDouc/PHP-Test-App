<?php

namespace TestApp\Core;

class Session
{
  private const TEMP_DATA_KEY = "temp-data";
  private const USER_KEY = "user";

  public function __construct()
  {
    $_SESSION[self::TEMP_DATA_KEY] ??= [];
  }

  public function getTempData(string $key): mixed
  {
    if (!array_key_exists($key, $_SESSION[self::TEMP_DATA_KEY]))
      return null;

    $data = $_SESSION[self::TEMP_DATA_KEY][$key];
    unset($_SESSION[self::TEMP_DATA_KEY][$key]);
    return $data;
  }

  public function setTempData(string $key, mixed $data): Session
  {
    $_SESSION[self::TEMP_DATA_KEY][$key] = $data;
    return $this;
  }

  public function getSuccessMessage(): string|null
  {
    return $this->getTempData("success");
  }

  public function setSuccessMessage(string $message): Session
  {
    return $this->setTempData("success", $message);
  }

  public function getErrorMessages(): array|null
  {
    return $this->getTempData("errors");
  }

  public function setErrorMessages(array $messages): Session
  {
    return $this->setTempData("errors", $messages);
  }

  public function getFormData(): array|null
  {
    return $this->getTempData("form-data");
  }

  public function setFormData(array $formData): Session
  {
    return $this->setTempData("form-data", $formData);
  }

  public function getUser(): array|null
  {
    return $_SESSION[self::USER_KEY] ?? null;
  }

  public function signIn(array $user): void
  {
    $_SESSION[self::USER_KEY] = $user;
  }

  public function signOut(): void
  {
    unset($_SESSION[self::USER_KEY]);
  }
}
