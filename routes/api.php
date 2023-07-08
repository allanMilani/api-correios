<?php

use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\PrePostagemController;
use App\Http\Controllers\RastreioController;
use App\Http\Controllers\SigepController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [PassportAuthController::class, 'register']);
Route::post('/login', [PassportAuthController::class, 'login']);


Route::group(['middleware' => 'auth:api'], function(){
    //User Authenticate
    Route::post('/logout', [PassportAuthController::class, 'logout']);
    Route::get('/user', [PassportAuthController::class, 'show']);
    Route::put('/reset', [PassportAuthController::class, 'resetPassword']);
});

//Rotas Correios
Route::post('verifica-disponibilidade-servico', [SigepController::class, 'checkServiceAvailability']);
Route::post('busca-cliente', [SigepController::class, 'findCliente']);
Route::post('consulta-cep', [SigepController::class, 'findCEP']);
Route::post('status-cartao-postagem', [SigepController::class, 'getStatusCard']);
Route::post('solicita-etiquetas', [SigepController::class, 'getLabel']);
Route::post('gera-digito-verificador-etiquetas', [SigepController::class, 'getCheckDigit']);
Route::post('fecha-plp', [SigepController::class, 'closePlp']);
Route::post('solicita-xml-plp', [SigepController::class, 'getXmlPlp']);
Route::post('bloquear-objeto', [SigepController::class, 'blockObject']);

//Prepostagem
Route::post('prepostagens', [PrePostagemController::class, 'getPrepostagens']);
Route::post('rastro', [RastreioController::class, 'getStatus']);