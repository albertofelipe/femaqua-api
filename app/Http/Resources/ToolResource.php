<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $method = $request->method();

        if($method=='POST'){
            return [ 
                'title' => $this->title,  
                'link' => $this->link,  
                'description' => $this->description,  
                'tags' => $this->whenLoaded('tags', fn () =>
                  $this->tags->pluck('name')->toArray(),
            ),
                'id' => $this->id 
            ];
        }

        return [
            'id' => $this->id,  
            'title' => $this->title,  
            'link' => $this->link,  
            'description' => $this->description,  
            'tags' => $this->whenLoaded('tags', fn () =>
              $this->tags->pluck('name')->toArray()
            )
        ];
    }
}
