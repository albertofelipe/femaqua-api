<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Tag;
use App\Models\Tool;

class ToolControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_with_tag_filter()
    {
        $tagNode = Tag::factory()->create(['name' => 'node']);
        $tagReact = Tag::factory()->create(['name' => 'react']);
        
        $toolNode = Tool::factory()->create();
        $toolNode->tags()->attach($tagNode);
        
        $toolReact = Tool::factory()->create(); 
        $toolReact->tags()->attach($tagReact); 
        
        $response = $this->getJson("/api/tools?tag=node");
        
        $response->assertOk()
                 ->assertJsonFragment(['title' => $toolNode->title]) 
                 ->assertJsonMissing(['title' => $toolReact->title]);
    }

    public function test_index_with_pagination()
    {
        Tool::factory()->count(15)->create();
        
        $response = $this->getJson("/api/tools?page=1");

        $response->assertOk()
                 ->assertJsonCount(10, 'data')
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'title', 'link', 'description', 'tags']
                     ]
                 ])
                 ->assertJsonFragment([
                     'current_page' => 1,
                     'per_page' => 10,
                 ]);
    }

    public function test_index_without_filters()
    {
        Tool::factory()->count(3)->create();
        
        $response = $this->getJson("/api/tools");
        
        $response->assertOk()
                 ->assertJsonCount(3, 'data')
                 ->assertJsonFragment(['title' => Tool::first()->title]);
    }

    public function test_index_no_results()
    {        
        $response = $this->getJson("/api/tools");
        
        $response->assertOk()
                 ->assertJsonCount(0, 'data');
    }
}
