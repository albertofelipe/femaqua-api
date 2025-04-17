<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="API de Ferramentas",
 *     version="1.0",
 *     description="Documentação da API responsável por endpoints relacionados a ferramentas."
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor principal"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
