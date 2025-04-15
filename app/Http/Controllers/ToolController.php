<?php

namespace App\Http\Controllers;

use App\Http\Resources\ToolCollection;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    
    public function index(Request $request) 
    {
        $tools = new ToolCollection(
            Tool::filterByRequest($request)
                ->paginate(10)
                ->appends($request->query())
            );

        return response()->json($tools, 200);
    }

}
