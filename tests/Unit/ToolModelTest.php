<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_request_sucessfull()
    {
        $tool1 = Tool::factory()->create();
        $tool2 = Tool::factory()->create();
        $tag = Tag::factory()->create(['name' => 'php']);
        
        $tool1->tags()->attach($tag);

        $filteredTools = Tool::filterByRequest('php')->get();

        $this->assertCount(1, $filteredTools);
        $this->assertEquals($tool1->id, $filteredTools->first()->id);
        $this->assertTrue($filteredTools->first()->tags->contains('name', 'php'));
    }

    public function test_filter_by_request_return_all_tools_when_no_tag_filter()
    {
        Tool::factory()->count(3)->create();

        $tools = Tool::filterByRequest(null)->get();

        $this->assertCount(3, $tools);
    }

    public function test_sync_tags_and_create_new_ones()
    {
        $tool = Tool::factory()->create();
        $existingTag = Tag::factory()->create(['name' => 'php']);
        $tagNames = ['php', 'javascript', 'laravel'];

        $tool->syncTags($tagNames);

        $this->assertCount(3, $tool->tags);
        $this->assertEquals(3, $tool->tags->count());

        $this->assertDatabaseHas('tags', ['name' => 'javascript']);
        $this->assertDatabaseHas('tags', ['name' => 'laravel']);
        $this->assertDatabaseHas('tool_tag', ['tool_id' => $tool->id]);
        }

    public function test_handle_empty_tags_array()
    {
        $tool = Tool::factory()->create();
        $tool->tags()->attach(Tag::factory()->create());

        $tool->syncTags([]);

        $this->assertCount(0, $tool->fresh()->tags);
    }

    public function test_return_tools_in_latest_order()
    {
        $oldTool = Tool::factory()->create(['created_at' => now()->subDay()]);
        $newTool = Tool::factory()->create(['created_at' => now()]);

        $tools = Tool::filterByRequest(null)->get();

        $this->assertEquals($newTool->id, $tools->first()->id);
    }
}
