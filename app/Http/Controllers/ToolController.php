<?php

namespace App\Http\Controllers;

use App\Exceptions\ToolNotFoundException;
use App\Http\Requests\ToolRequest;
use App\Http\Resources\ToolCollection;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

    public function store(ToolRequest $request) 
    {
        $tool = Tool::create([
            ...$request->validated(),
            'user_id' => $request->user()->id
        ]);

        $tool->syncTags($request->input('tags', []));

        return response()->json(new ToolResource($tool), 201);
    }

    public function show($id) 
    {
        $tool = Tool::with('tags')
                    ->find($id) 
                    ?? throw new ToolNotFoundException();
                    
        $this->authorize('view', $tool);

        return response()->json(new ToolResource($tool), 200);
    }

}
