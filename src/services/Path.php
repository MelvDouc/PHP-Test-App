<?php

namespace TestApp\Services;

class Path
{
  private static function split(string $str): array
  {
    return array_filter(explode("/", $str), fn ($str) => $str !== "");
  }

  private static function startsWithColon(string $str): bool
  {
    return str_starts_with($str, ":");
  }

  public static function compare(string $dynamicPath, string $staticPath): bool
  {
    $dynamicPath = self::split($dynamicPath);
    $staticPath = self::split($staticPath);

    if (count($dynamicPath) !== count($staticPath))
      return false;

    foreach ($dynamicPath as $i => $value)
      if (!self::startsWithColon($value) && $value !== $staticPath[$i])
        return false;

    return true;
  }

  public static function getParamsMap(string $dynamicPath, string $staticPath): array
  {
    $map = [];
    $dynamicPath = self::split($dynamicPath);
    $staticPath = self::split($staticPath);

    foreach ($dynamicPath as $i => $segment)
      if (self::startsWithColon($segment))
        $map[substr($segment, 1)] = $staticPath[$i];

    return $map;
  }

  public static function addContext(string $path, array $context): string
  {
    $path = self::split($path);

    foreach ($path as $i => $segment)
      if (self::startsWithColon($segment))
        $path[$i] = $context[substr($segment, 1)];

    return implode("/", $path);
  }
}
