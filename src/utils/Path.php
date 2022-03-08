<?php

namespace TestApp\Utils;

class Path
{
  public static function split(string $value): array
  {
    return array_reduce(
      explode("/", $value),
      function ($acc, $item) {
        if ($item !== "")
          $acc[] = $item;
        return $acc;
      },
      []
    );
  }

  public static function compare(string $dynamicPath, string $staticPath): bool
  {
    $dynamicPath = self::split($dynamicPath);
    $staticPath = self::split($staticPath);

    if (count($dynamicPath) !== count($staticPath))
      return false;

    foreach ($dynamicPath as $i => $value)
      if (!str_contains($value, ":") && $value !== $staticPath[$i])
        return false;

    return true;
  }

  public static function getParamsMap(string $dynamicPath, string $staticPath): array
  {
    $map = [];
    $dynamicPath = self::split($dynamicPath);
    $staticPath = self::split($staticPath);

    foreach ($dynamicPath as $i => $segment)
      if (str_contains($segment, ":"))
        $map[substr($segment, 1)] = $staticPath[$i];

    return $map;
  }

  public static function addContext(string $path, array $context): string
  {
    $path = self::split($path);

    foreach ($path as $i => $segment)
      if (str_contains($segment, ":"))
        $path[$i] = $context[substr($segment, 1)];

    return implode("/", $path);
  }
}