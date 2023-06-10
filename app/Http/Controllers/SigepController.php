<?php

namespace App\Http\Controllers;

use App\Utils\ObjectUtil;
use App\Utils\XMLUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SoapClient;
use Throwable;

class SigepController extends Controller
{
    private $soap;

    public function __construct()
    {
        $this->soap = new SoapClient(env('URL_SIGEP'));
    }

    public function test(Request $request){
        try {
            $soapArgs = [
                'cep' => '70002900'
            ];

            dd($this->soap->consultaCEP($soapArgs));
        } catch(Throwable $e){
            dd($e->getMessage());
        }
    }

    private function executeService($sService, $aData, $aRemoveElement = []){
        $aResult = $this->soap->$sService($aData);

        if(empty($aResult->return)) 
            return response()->json(['message' => 'Nenhum registro foi encontrado :('], 200);
        
        return response()->json([
                'message' => 'Registros encontrado com sucesso', 
                'data' => ObjectUtil::ConvertFromLatin1ToUtf8Recursively($aResult->return, $aRemoveElement)
            ],
            200,
            [],
            JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE
        );
    }

    public function checkServiceAvailability(Request $oRequest):JsonResponse
    {
        try {
            $aErrors = [];

            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';
            
            if(empty($oRequest->cod_administrativo)) $aErrors[] = 'Código Administrativo';

            if(empty($oRequest->numero_servico)) $aErrors[] = 'Número Servico';

            if(empty($oRequest->cep_origem)) $aErrors[] = 'CEP Origem';

            if(empty($oRequest->cep_destino)) $aErrors[] = 'CEP Destino';
            
            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('verificaDisponibilidadeServico', [
                'usuario'           => $oRequest->usuario,
                'senha'             => $oRequest->senha,
                'codAdministrativo' => $oRequest->cod_administrativo,
                'numeroServico'     => $oRequest->numero_servico,
                'cepOrigem'         => $oRequest->cep_origem,
                'cepDestino'        => $oRequest->cep_destino
            ]);
        } catch (\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function findCliente(Request $oRequest): JsonResponse
    {
        try{
            $aErrors = [];

            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';
            
            if(empty($oRequest->id_contrato)) $aErrors[] = 'Id do Contrato';

            if(empty($oRequest->id_cartao_postagem)) $aErrors[] = 'Id do Cartão Postagem';
            
            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('buscaCliente', [
                'usuario'          => $oRequest->usuario,
                'senha'            => $oRequest->senha,
                'idContrato'       => $oRequest->id_contrato,
                'idCartaoPostagem' => $oRequest->id_cartao_postagem
            ], ['chancela']);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function findCEP(Request $oRequest): JsonResponse
    {
        try {
            if(empty($oRequest->cep))
                return response()->json(['message' => 'Por favor informe o CEP'], 500);    

            return $this->executeService('consultaCEP', [
                'cep' => $oRequest->cep
            ]);
        } catch (\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getStatusCard(Request $oRequest):JsonResponse
    {
        try {
            $aErrors = [];

            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';
            
            if(empty($oRequest->numero_cartao_postagem)) $aErrors[] = 'Número cartão postagem';
            
            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('getStatusCartaoPostagem', [
                'usuario'              => $oRequest->usuario,
                'senha'                => $oRequest->senha,
                'numeroCartaoPostagem' => $oRequest->numero_cartao_postagem
            ]);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getLabel(Request $oRequest): JsonResponse
    {
        try {
            $aErrors = [];

            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';
            
            if(empty($oRequest->tipo_destinatario)) $aErrors[] = 'Tipo do destinatario';

            if(empty($oRequest->identificador)) $aErrors[] = 'Identificador';

            if(empty($oRequest->id_servico)) $aErrors[] = 'Id do serviço';

            if(empty($oRequest->qtde_etiquetas)) $aErrors[] = 'Quantidade etiquetas';

            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('solicitaEtiquetas', [
                'usuario'          => $oRequest->usuario,
                'senha'            => $oRequest->senha,
                'tipoDestinatario' => $oRequest->tipo_destinatario,
                'identificador'    => $oRequest->identificador,
                'idServico'        => $oRequest->id_servico,
                'qtdEtiquetas'     => $oRequest->qtde_etiquetas
            ]);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getCheckDigit(Request $oRequest): JsonResponse
    {
        try {
            $aErrors = [];
            
            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';

            if(empty($oRequest->etiqueta)) $aErrors[] = 'Etiqueta(s)';

            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('geraDigitoVerificadorEtiquetas', [
                'usuario'   => $oRequest->usuario,
                'senha'     => $oRequest->senha,
                'etiquetas' => $oRequest->etiqueta
            ]);
        } catch (\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function closePlp(Request $oRequest): JsonResponse
    {
        try{
            $aErrors = [];
            
            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';

            if(empty($oRequest->id_plp_cliente)) $aErrors[] = 'ID da PLP';

            if(empty($oRequest->cartao_postagem)) $aErrors[] = 'Cartão Postagem';
            
            if(empty($oRequest->lista_etiquetas)) $aErrors[] = 'Etiqueta(s)';
            
            if(empty($oRequest->xml)) $aErrors[] = 'XML';

            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            $sXml = XMLUtil::createXml($oRequest->xml, [
                'nome_remetente',
                'logradouro_remetente',
                'complemento_remetente',
                'bairro_remetente',
                'cidade_remetente',
                'telefone_remetente',
                'fax_remetente',
                'email_remetente',
                'nome_destinatario',
                'telefone_destinatario',
                'celular_destinatario',
                'email_destinatario',
                'logradouro_destinatario',
                'complemento_destinatario',
                'bairro_destinatario',
                'cidade_destinatario',
                'cep_destinatario'
            ], 'correioslog');
            
            return $this->executeService('fechaPlpVariosServicos', [
                'usuario'        => $oRequest->usuario,
                'senha'          => $oRequest->senha,
                'idPlpCliente'   => $oRequest->id_plp_cliente,
                'cartaoPostagem' => $oRequest->cartao_postagem,
                'listaEtiquetas' => $oRequest->lista_etiquetas,
                'xml'            => $sXml
            ]);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getXmlPlp(Request $oRequest): JsonResponse
    {
        try{
            $aErrors = [];
            
            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';

            if(empty($oRequest->id_plp_master)) $aErrors[] = 'Id da PLP';

            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('solicitaXmlPlp', [
                'usuario'     => $oRequest->usuario,
                'senha'       => $oRequest->senha,
                'idPlpMaster' => $oRequest->id_plp_master
            ]);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function blockObject(Request $oRequest): JsonResponse
    {
        try{
            $aErrors = [];
            
            if(empty($oRequest->usuario)) $aErrors[] = 'Usuário';
            
            if(empty($oRequest->senha)) $aErrors[] = 'Senha';

            if(empty($oRequest->acao)) $aErrors[] = 'Ação';

            if(empty($oRequest->tipo_bloqueio)) $aErrors[] = 'Tipo do bloqueio';

            if(empty($oRequest->id_plp)) $aErrors[] = 'Id da PLP';

            if(empty($oRequest->numero_etiqueta)) $aErrors[] = 'Número etiqueta';

            if(!empty($aErrors))
                return response()->json(['message' => 'Por favor preencha corretamente o(s) seguinte(s) campo(s): ' . join(', ', $aErrors)], 400);

            return $this->executeService('bloquearObjeto', [
                'usuario'         => $oRequest->usuario,
                'senha'           => $oRequest->senha,
                'acao'            => $oRequest->acao,
                'tipoBloqueio'    => $oRequest->tipo_bloqueio,
                'idPlp'           => $oRequest->id_plp,
                'numeroEtiqueta' => $oRequest->numero_etiqueta
            ]);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }        
    }

}   
