<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkCreateToolRequest;
use App\Http\Requests\CreateToolRequest;
use App\Http\Requests\UpdateToolRequest;
use App\Http\Resources\ToolCollection;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;


/**
 * @OA\Tag(
 *     name="Ferramentas",
 *     description="Endpoints relacionados às ferramentas disponíveis"
 * )
 */
class ToolController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/tools",
     *     summary="Listar todas as ferramentas",
     *     description="Retorna uma lista de ferramentas com paginação. Opcionalmente pode filtrar por tags.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filtrar ferramentas por tag",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de ferramentas retornada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="link", type="string"),
     *                    @OA\Property(
     *                    property="tags",
     *                   type="array",
     *                   @OA\Items(type="string", example="php"),
     *                   example={"php", "laravel", "mysql"}
     *                 )
     *                )
     *            ),
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="next_page", type="integer", nullable=true),
     *                 @OA\Property(property="previous_page", type="integer", nullable=true),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page') ?? 10;
        $tag = $request->query('tag') ?? null;

        $tools = new ToolCollection(
            Tool::filterByRequest($tag)
                ->paginate(perPage: $perPage)
                ->appends($request->query())
        );

        return response()->json($tools, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tools",
     *     summary="Criar uma nova ferramenta",
     *     description="Cria uma nova ferramenta com os dados fornecidos.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da nova ferramenta",
     *         @OA\JsonContent(
     *             required={"title", "description", "link"},
     *             @OA\Property(property="title", type="string", example="Nova Ferramenta"),
     *             @OA\Property(property="description", type="string", example="Descrição da nova ferramenta"),
     *             @OA\Property(property="link", type="string", example="http://exemplo.com"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"docker", "jenkins"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tool successfully created",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="laravel"),
     *             @OA\Property(property="link", type="string", example="https://www.laravel.com"),
     *             @OA\Property(property="description", type="string", example="laravel"),
     *             @OA\Property(
     *                property="tags",
     *               type="array",
     *              @OA\Items(type="string", example="php"),
     *               example={"php", "laravel", "mysql"}
     *            ),
     *             @OA\Property(property="id", type="integer", example=1)
     *         ) 
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos fornecidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The title is required."),
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
    public function store(CreateToolRequest $request)
    {
        $tool = Tool::create([
            ...$request->validated(),
            'user_id' => $request->user()->id
        ]);

        $tool->syncTags($request->input('tags', []));

        return response()->json(new ToolResource($tool), 201);
    }
    /**
     * @OA\Post(
     *     path="/api/tools/bulk",
     *     summary="Criar várias ferramentas de uma só vez",
     *     description="Cria várias ferramentas com os dados fornecidos em um array.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Um array de ferramentas a serem criadas",
     *         @OA\JsonContent(
     *             required={"tools"},
     *             @OA\Property(
     *                 property="tools",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"title", "description", "link"},
     *                     @OA\Property(property="title", type="string", example="laravel"),
     *                     @OA\Property(property="description", type="string", example="laravel"),
     *                     @OA\Property(property="link", type="string", example="http://example.com"),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"php","laravel"})
     *                 ),
     *                 example={
     *                     {"title":"laravel","description":"laravel","link":"http://example.com","tags":{"php","laravel"}},
     *                     {"title":"symfony","description":"framework","link":"http://symfony.com","tags":{"php","symfony"}}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ferramentas criadas com sucesso",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="title", type="string", example="laravel"),
     *                 @OA\Property(property="link", type="string", example="https://www.laravel.com"),
     *                 @OA\Property(property="description", type="string", example="laravel"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"php","laravel","mysql"}),
     *                 @OA\Property(property="id", type="integer", example=1)
     *             ),
     *              example={
     *                     {"title":"laravel","description":"laravel","link":"http://example.com","tags":{"php","laravel"}, "id":1},
     *                     {"title":"symfony","description":"framework","link":"http://symfony.com","tags":{"php","symfony"}, "id":2}
     *                 }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos fornecidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The title is required.")
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
    public function bulkStore(BulkCreateToolRequest $request)
    {
        $validatedData = $request->validated();
        $tools = DB::transaction(function () use ($validatedData) {
            return $this->createToolsWithTags($validatedData['tools']);
        });

        return response()->json(ToolResource::collection($tools), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tools/{id}",
     *     summary="Obter uma ferramenta específica",
     *     description="Retorna os detalhes de uma ferramenta específica com base no ID fornecido. O usuário deve ser o proprietário da ferramenta.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da ferramenta a ser buscada",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ferramenta encontrada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="laravel"),
     *             @OA\Property(property="link", type="string", example="https://www.laravel.com"),
     *             @OA\Property(property="description", type="string", example="laravel"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="php"),
     *                 example={"php", "laravel", "mysql"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found")
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
    public function show($id)
    {
        $tool = Tool::with('tags')
            ->where('id', $id)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        $this->authorize('view', $tool);

        return response()->json(new ToolResource($tool), 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/tools/{id}",
     *     summary="Deletar uma ferramenta",
     *     description="Deleta uma ferramenta específica com base no ID fornecido. O usuário deve ser o proprietário da ferramenta.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="O ID da ferramenta a ser deletada",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ferramenta deletada com sucesso",
     *         @OA\JsonContent(type="array", @OA\Items()))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found")
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
    public function destroy($id)
    {
        $tool = Tool::with('tags')
            ->where('id', $id)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        $this->authorize('delete', $tool);

        $tool->delete();
        return response()->json([], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/tools/{id}",
     *     summary="Atualizar uma ferramenta",
     *     description="Atualizar uma ferramenta com base no ID fornecido. O usuário deve ser o proprietário da ferramenta.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="O ID da ferramenta a ser atualizada",
     *         @OA\Schema(type="integer", example=4)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "link", "description"},
     *             @OA\Property(property="title", type="string", example="laravel"),
     *             @OA\Property(property="link", type="string", example="https://www.laravel.com"),
     *             @OA\Property(property="description", type="string", example="laravel"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="php"),
     *                 example={"php", "laravel", "mysql"}
     *             )
     *         )
     *     ),
     *         @OA\Response(
     *         response=201,
     *         description="Tool successfully created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="laravel"),
     *             @OA\Property(property="link", type="string", example="https://www.laravel.com"),
     *             @OA\Property(property="description", type="string", example="laravel"),
     *             @OA\Property(
     *                property="tags",
     *               type="array",
     *              @OA\Items(type="string", example="php"),
     *               example={"php", "laravel", "mysql"}
     *            )
     *         )
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found")
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
     *
     *
     *
     * @OA\Patch(
     *     path="/api/tools/{id}",
     *     summary="Atualizar parcialmente uma ferramenta",
     *     description="Atualizar parcialmente uma ferramenta com base no ID fornecido. O usuário deve ser o proprietário da ferramenta.",
     *     tags={"Tools"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="O ID da ferramenta a ser atualizada",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="laravel atualizado"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="php"),
     *                 example={"php atualizado", "laravel", "mysql"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tool successfully created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="laravel atualizado"),
     *             @OA\Property(property="link", type="string", example="https://www.laravel.com"),
     *             @OA\Property(property="description", type="string", example="laravel"),
     *             @OA\Property(
     *                property="tags",
     *               type="array",
     *              @OA\Items(type="string", example="php"),
     *               example={"php atualizado", "laravel", "mysql"}
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found")
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

    public function update(UpdateToolRequest $request, $id)
    {
        $tool = Tool::with('tags')
            ->where('id', $id)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        $this->authorize('update', $tool);

        $tool->update($request->validated());

        if($request->has('tags')) {
            $tool->syncTags($request->input('tags', []));
        }

        return response()->json(new ToolResource($tool), 200);
    }

    private function createToolsWithTags(array $toolsData)
    {
        return collect($toolsData)->map(function ($toolData) {
            $tool = Tool::create([
                ...$toolData,
                'user_id' => request()->user()->id,
            ]);

            $tool->syncTags($toolData['tags'] ?? []);

            return $tool;
        });
    }
}
