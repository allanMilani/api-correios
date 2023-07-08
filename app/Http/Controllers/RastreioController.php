<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthCorreios;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RastreioController extends Controller
{
    public $sUrl;
    public $oAuthCorreios;

    public function __construct()
    {   
        $this->sUrl = env('URL_RASTREIO');
        $this->oAuthCorreios = new AuthCorreios();
    }

    public function getStatus(Request $oRequest):JsonResponse 
    {
        if(empty($oRequest->objeto)) return response()->json(
            [
                'message' => 'Por favor informe o objeto'
            ],
            400,
            [],
            JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
        );

        $oDadosAuth = $this->oAuthCorreios->tokenSaved(
            $oRequest->user, 
            $oRequest->password,
            null,
            $oRequest->cartao,
            $oRequest->cnpj
        );

        if(empty($oDadosAuth)) return response()->json([
                'message' => 'Por favor informe todos os dados corretamentes',
            ],
            400,
            [],
            JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
        );

        if(empty($oDadosAuth['token'])) return response()->json([
                'message' => 'Ops, ocorreu um erro ao autenticar por favor tente novamente mais tarde'
            ],
            500,
            [],
            JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
        );

        try{
            $oClient = new Client();

            $oResponse = $oClient->request(
                'GET',
                $this->sUrl . '/v1/objetos/' . $oRequest->objeto,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $oDadosAuth['token']
                    ],
                    'query' => [
                        'resultado' => !empty($oRequest->resultado)? $oRequest->resultado : 'U'
                    ]
                ]
            );

            $aDados = $oResponse->getStatusCode() == 200 ? json_decode($oResponse->getBody()->getContents(), true) : [];
            return response()->json(
                $aDados,
                $oResponse->getStatusCode(),
                [],
                JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
            );
        } catch(\Throwable $e){
            return response()->json([
                    'message' => $e->getMessage()
                ],
                500,
                [],
                JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
            );
        }
    }

}
