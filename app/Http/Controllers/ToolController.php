<?php

namespace App\Http\Controllers;

use App\Exceptions\ToolNotFoundException;
use App\Http\Requests\BulkCreateToolRequest;
use App\Http\Requests\CreateToolRequest;
use App\Http\Requests\UpdateToolRequest;
use App\Http\Resources\ToolCollection;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller
{
    use AuthorizesRequests;
    
    public function index(Request $request) 
    {
        $tools = new ToolCollection(
            Tool::filterByRequest($request)
                ->paginate(10)
                ->appends($request->query())
            );

        return response()->json($tools, 200);
    }

    public function store(CreateToolRequest $request) 
    {
        $tool = Tool::create([
            ...$request->validated(),
            'user_id' => $request->user()->id
        ]);

        $tool->syncTags($request->input('tags', []));

        return response()->json(new ToolResource($tool), 201);
    }

    public function bulkStore(BulkCreateToolRequest $request) 
    {
        $validatedData = $request->validated();
        $tools = DB::transaction(function () use ($validatedData) {
            return $this->createToolsWithTags($validatedData['tools']);
        });
    
        return response()->json(ToolResource::collection($tools), 201);
    }
    

    public function show($id) 
    {
        $tool = Tool::where('id', $id)
                ->where('user_id', request()->user()->id)
                ->firstOrFail();

        $this->authorize('view', $tool); 

        return response()->json(new ToolResource($tool), 200);
    }

    public function destroy($id) 
    {
        $tool = Tool::where('id', $id)
                ->where('user_id', request()->user()->id)
                ->firstOrFail();

        $this->authorize('delete', $tool);

        $tool->delete();
        return response()->json(['message' => 'Tool deleted successfully'], 200);
    }

    public function update(UpdateToolRequest $request, $id) 
    {
        $tool = Tool::where('id', $id)
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
