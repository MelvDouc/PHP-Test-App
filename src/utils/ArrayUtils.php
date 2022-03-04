<?php

namespace TestApp\Utils;

class ArrayUtils
{
  public static function every(array $arr, callable $predicate)
  {
    foreach ($arr as $key => $value)
      if (!$predicate($value, $key))
        return false;

    return true;
  }
}
