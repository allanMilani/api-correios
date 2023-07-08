<?php

namespace App\Http\Services;

use App\Models\TokenCorreios;
use GuzzleHttp\Client;

class AuthCorreios{

  public function tokenSaved($user, $password, $contract, $card, $cnpj){
    $token = TokenCorreios::where([
      'cnpj' => $cnpj
    ])
    ->get()
    ->last();

    if(!empty($token) && strtotime($token->expira_em) >= strtotime(date('Y-m-d H:i:s'))){
      return $token->toArray();
    }

    $oTokenCorreios = null;
    if(!empty($contract)){
      $oTokenCorreios = $this->authContrato($user, $password, $contract);
    } else if(!empty($card)){
      $oTokenCorreios = $this->authCartao($user, $password, $card);
    }
    
    if(empty($oTokenCorreios)) return false;
    
    try{
      TokenCorreios::updateOrCreate(
        ['cnpj' => $cnpj],
        [
          'user' => $user,
          'correios_id' => !empty($oTokenCorreios->id) ? $oTokenCorreios->id : null,
          'cnpj' => $cnpj,
          'emissao' => !empty($oTokenCorreios->emissao) ? $oTokenCorreios->emissao : null,
          'expira_em' => !empty($oTokenCorreios->expiraEm) ? $oTokenCorreios->expiraEm : null,
          'token' => !empty($oTokenCorreios->token) ? $oTokenCorreios->token : null
        ]
      );

      return (array) $oTokenCorreios;
    } catch(\Throwable $e){
      return false;
    }
  }

  public function auth($user, $password){
    if(empty($user) || empty($password)) return false;

    $oClient = new Client();

    try{
      $oResponse = $oClient->request(
        'POST',
        env('URL_AUTH') . '/v1/autentica',
        [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($user.':'.$password)
          ]
        ]
      );

      $aDados = ($oResponse->getStatusCode() >= 200 && $oResponse->getStatusCode() <= 300) ? json_decode($oResponse->getBody()->getContents()) : [];
      return $aDados;
    } catch (\Throwable $e){
      return $e->getMessage();
    }
  }

  public function authContrato($user, $password, $card){
    if(empty($user) || empty($password) || empty($card)) return false;

    $oClient = new Client();

    try{
      $oResponse = $oClient->request(
        'POST',
        env('URL_AUTH') . '/v1/autentica/contrato',
        [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($user.':'.$password)
          ],
          'json' => [
            'numero' => $card
          ]
        ]
      );

      $aDados = ($oResponse->getStatusCode() >= 200 && $oResponse->getStatusCode() <= 300) ? json_decode($oResponse->getBody()->getContents()) : [];
      return $aDados;
    } catch(\Throwable $e){
      return $e->getMessage();
    }
  }

  public function authCartao($user, $password, $card){
    if(empty($user) || empty($password) || empty($card)) return false;

    $oClient = new Client();

    try{
      $oResponse = $oClient->request(
        'POST',
        env('URL_AUTH') . '/v1/autentica/cartaopostagem',
        [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($user.':'.$password)
          ],
          'json' => [
            'numero' => $card
          ]
        ]
      );

      $aDados = ($oResponse->getStatusCode() >= 200 && $oResponse->getStatusCode() <= 300) ? json_decode($oResponse->getBody()->getContents()) : [];
      return $aDados;
    } catch(\Throwable $e){
      return $e->getMessage();
    }
  }

}