<?php

namespace TestApp\Utils;

class StringUtils
{
  private const CHARS = "0123456789abcdfghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";

  public static function getRandomString(int $length): string
  {
    $str = "";

    while (strlen($str) < $length)
      // strlen(self::CHARS) === 62
      $str .= self::CHARS[random_int(0, 61)];

    return $str;
  }

  public static function checkPasswordStrength(string $password): array
  {
    $errors = [];
    $length = strlen($password);
    if ($length < 8)
      $errors[] = "Mot de passe trop court : 8 caractères minimum.";
    if ($length > 30)
      $errors[] = "Mot de passe trop long : 30 caractères maximum.";
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])/", $password))
      $errors[] = "Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.";

    return $errors;
  }
}
