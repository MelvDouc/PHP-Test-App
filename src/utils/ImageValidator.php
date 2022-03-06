<?php

namespace TestApp\Utils;

class ImageValidator
{
  private const MAX_SIZE = 2e6;
  private static array $ALLOWED_TYPES = ["jpg", "jpeg", "png", "gif"];

  public static function check(mixed $image): array
  {
    $errors = [];

    if (
      !is_array($image)
      || !isset($image["error"])
      || !isset($image["type"])
      || !isset($image["size"])
      || $image["error"] !== 0
    ) {
      $errors[] = "Fichier d'image manquant ou invalide.";
      return $errors;
    }

    $type = preg_replace("/^image\//", "", $image["type"]);

    if ($image["size"] > self::MAX_SIZE)
      $errors[] = "Le fichier ne doit pas dépasser 2 MO.";

    if (!in_array($type, self::$ALLOWED_TYPES)) {
      $formats = implode(" / ", self::$ALLOWED_TYPES);
      $errors[] = "Format d'image invalide. Formats autorisés : $formats.";
    }

    return $errors;
  }
}
