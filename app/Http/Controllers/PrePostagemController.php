<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthCorreios;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrePostagemController extends Controller
{
    public $sUrl;
    public $oAuthCorreios;

    public function __construct()
    {   
        $this->sUrl = env('URL_PREPOSTAGEM');
        $this->oAuthCorreios = new AuthCorreios();
    }

    public function getPrepostagens(Request $oRequest):JsonResponse
    {
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
            $iCount = 0;
            do{
                $oClient = new Client();

                $oResponse = $oClient->request(
                    'GET', 
                    $this->sUrl . '/v2/prepostagens',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $oDadosAuth['token']
                        ],
                        'query' => [
                            'status' => ((!empty($oRequest->status))? $oRequest->status : ''),
                            'tipoObjeto' => ((!empty($oRequest->tipoObjeto))? $oRequest->tipoObjeto : ''),
                            'page' => ((!empty($oRequest->page))? $oRequest->page : ''),
                            'size' => ((!empty($oRequest->size))? $oRequest->size : ''),
                        ]
                    ]
                );
                if($oResponse->getStatusCode() == 200){
                    $aDados = json_decode($oResponse->getBody()->getContents(), true);
                    return response()->json(
                        $aDados,
                        $oResponse->getStatusCode(),
                        [],
                        JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
                    );
                }

                $iCount++;

                $oDadosAuth = $this->oAuthCorreios->tokenSaved(
                    $oRequest->user, 
                    $oRequest->password,
                    null,
                    $oRequest->cartao,
                    $oRequest->cnpj,
                    true
                );
            } while($oResponse->getStatusCode() == 401 && $iCount > 3);
            
            return response()->json(
                ['message' => 'Ops, não foi possivel realizar a busca, por favor tente novamente mais tarde'],
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
