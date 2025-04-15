<?php

namespace App\Http\Controllers;

use App\Http\Resources\ToolCollectionResource;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    
    public function index() 
    {
        return new ToolCollectionResource(Tool::with('tags')->paginate(10));
    }

}
