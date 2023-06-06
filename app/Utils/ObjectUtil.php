<?php

namespace App\Utils;

class ObjectUtil{
  public static function ConvertFromLatin1ToUtf8Recursively($objetc){
    if (is_string($objetc)) {
      return trim(mb_convert_encoding($objetc, 'UTF-8', 'UTF-8'));
    } elseif (is_array($objetc)) {
        $ret = [];
        foreach ($objetc as $i => $d) $ret[ $i ] = self::ConvertFromLatin1ToUtf8Recursively($d);

        return $ret;
    } elseif (is_object($objetc)) {
        foreach ($objetc as $i => $d) $objetc->$i = self::ConvertFromLatin1ToUtf8Recursively($d);

        return $objetc;
    } else {
        return $objetc;
    }
  }
}