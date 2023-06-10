<?php

namespace App\Utils;

class ObjectUtil{
  public static function ConvertFromLatin1ToUtf8Recursively($objetc, $aRemoveElement){
    if (is_string($objetc)) {
      return trim(mb_convert_encoding($objetc, 'UTF-8', 'UTF-8'));
    } elseif (is_array($objetc)) {
        $ret = [];
        foreach ($objetc as $i => $d) {
          if(in_array($i, $aRemoveElement) && !is_array($objetc[$i])) {
            unset($objetc[$i]);
            continue;
          }

          $ret[ $i ] = self::ConvertFromLatin1ToUtf8Recursively($d, $aRemoveElement);
        }

        return $ret;
    } elseif (is_object($objetc)) {
        foreach ($objetc as $i => $d) {
          if(in_array($i, $aRemoveElement) && !is_array($objetc->$i)) {
            unset($objetc->$i);
            continue;
          }

          $objetc->$i = self::ConvertFromLatin1ToUtf8Recursively($d, $aRemoveElement);
        }

        return $objetc;
    } else {
        return $objetc;
    }
  }
}