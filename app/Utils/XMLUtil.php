<?php

namespace App\Utils;

use DOMDocument;

class XMLUtil {
  public static function createXml($aData, $aKeyCData, $sRootNode){
    $oDom = new DOMDocument('1.0', 'ISO-8859-1');
    $oDom->formatOutput = true;

    $oRootNode = $oDom->createElement($sRootNode);
    
    foreach($aData as $key => $value){
      if(is_array($value) && in_array("array", array_map('gettype', $value))){
         foreach($value as $aValueInterno){
          $oNodeCopy = $oDom->createElement($key);
          $aValuesParent = self::createNodes($oDom, null, $aValueInterno, $aKeyCData, $oNodeCopy);
          $oRootNode->appendChild($aValuesParent);
        }
        continue;
      }else if(is_array($value)){
        $aValuesParent = self::createNodes($oDom, $key, $value, $aKeyCData);
        $oRootNode->appendChild($aValuesParent);
        continue;
      }

      $oNode = $oDom->createElement($key);
      if(!is_null($value)){
        $oNode->appendChild(self::createValueNode($oDom, $value, $key, $aKeyCData));
      }
      $oRootNode->appendChild($oNode);
    }

    $oDom->appendChild($oRootNode);
    return $oDom->saveXML();
  }

  private static function createNodes(&$oDom, $sKey, $aValues, $aKeyCData, $oParentNode = null){  
    $oNode = (!empty($oParentNode)) ? $oParentNode : $oDom->createElement($sKey);

    foreach($aValues as $key => $value){
      try{
        if(is_array($value) && !is_numeric($key)){
          
          if(self::verifyKeyArray($value)){
            foreach($value as $sValue){
              $oNodeAux = $oDom->createElement($key);
              $oNodeAux->appendChild(self::createValueNode($oDom, $sValue, $key, $aKeyCData));
              $oNode->appendChild($oNodeAux);
            }
            continue;
          }

          $aValuesParent = self::createNodes($oDom, $key, $value, $aKeyCData);
          $oNode->appendChild($aValuesParent);
          continue;
        } 
  
        $oNodeAux = $oDom->createElement($key);
        if(!is_null($value)){
          $oNodeAux->appendChild(self::createValueNode($oDom, $value, $key, $aKeyCData));
        }
  
        $oNode->appendChild($oNodeAux);
      } catch (\Throwable $e){
        dd($e->getMessage());
      }
    }

    return $oNode;
  }

  private static function createValueNode($oDom, $value, $key, $aKeyCData){
    $sValueFormated = trim(mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1'));
    return (in_array($key, $aKeyCData))? $oDom->createCDATASection($sValueFormated) : $oDom->createTextNode($sValueFormated);
  }

  private static function verifyKeyArray($aData){
      return empty(array_filter(array_keys($aData), 'is_string'));
  } 

  public static function xmlToJson($sXml){
    $sSimpleXml = simplexml_load_string($sXml);
    $sJson = json_encode($sSimpleXml, JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES);

    return json_decode($sJson, true);
  }

}