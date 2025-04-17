<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Autenticação e Autorização",
 *     description="Endpoints relacionados a Autenticação e Autorização de usuários"
 * )
 */
class AuthController extends Controller
{
    
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar um novo usuário",
     *     description="Registra um novo usuário com base nos dados fornecidos, loga e retorna o token criado.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password", "name"},
     *             @OA\Property(property="name", type="string", example="user"),
     *             @OA\Property(property="email", type="string", example="user@examplee.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", type="object", 
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="user"),
     *                 @OA\Property(property="email", type="string", example="user@examplee.com")
     *             ),
     *             @OA\Property(property="authorization", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *                 @OA\Property(property="type", type="string", example="bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The email field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input data")
     *         )
     *     )
     * )
     */
    public function register(AuthRegisterRequest $request)
    {
        $credentials = $request->validated();

        $user = User::create($credentials);
        $token = Auth::attempt($credentials);

        return $this->respondWithToken($token, $user, 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Realizar login de um usuário",
     *     description="Autentica um usuário e retorna um token de acesso.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@examplee.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário autenticado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User logged successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="user"),
     *                 @OA\Property(property="email", type="string", example="user@examplee.com")
     *             ),
     *             @OA\Property(property="authorization", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="type", type="string", example="bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The email field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid Credentials")
     *         )
     *     )
     * )
     */
    public function login(AuthLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $token = Auth::attempt($credentials);

        return $this->respondWithToken($token, $user, 'User logged successfully', 200);
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Realizar logout do usuário",
     *     description="Desloga o usuário e invalida o token de acesso.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Usuário deslogado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Obter informações do usuário autenticado",
     *     description="Retorna os dados do usuário atualmente autenticado.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informações do usuário autenticado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=3),
     *             @OA\Property(property="name", type="string", example="alberto"),
     *             @OA\Property(property="email", type="string", example="aaaass@teste.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function me()
    {
        return response()->json(new UserResource(Auth::user()), 200);
    }

    private function respondWithToken($token, User $user, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => new UserResource($user),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ], $code);
    }
}
