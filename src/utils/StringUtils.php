<?php

class StringUtils
{
  private const CHARS = "0123456789abcdfghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";

  public static function getRandomString(int $length): string
  {
    $str = "";

    while (strlen($str) < $length)
      $str .= self::CHARS[random_int(0, 62)];

    return $str;
  }
}
