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
}
