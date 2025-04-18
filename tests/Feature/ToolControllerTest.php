<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Tag;
use App\Models\Tool;
use App\Models\User;

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

    public function test_store_successfull()
    {
        $user = $this->createAuthenticatedUser();

        $toolData = $this->getValidToolData(['user_id' => $user->id]);
        
        $response = $this->postJson("/api/tools", $toolData);
        
        $response->assertCreated()
                 ->assertJsonFragment(['title' => $toolData['title']])
                 ->assertJsonFragment(['link' => $toolData['link']])
                 ->assertJsonFragment(['description' => $toolData['description']]);

        $this->assertDatabaseHas('tools',[
            'title' => $toolData['title'],
            'link' => $toolData['link'],
            'description' => $toolData['description'],
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('tags', [
            'name' => 'test-tag',
        ]);
    }

    public function test_store_with_invalid_data()
    {
        $this->createAuthenticatedUser();

        $toolData = $this->getInvalidToolData();
        
        $response = $this->postJson("/api/tools", $toolData);
        
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['title', 'link', 'description']);
    }
    
    public function test_bulk_store_successfull()
    {
        $user = $this->createAuthenticatedUser();

        $toolsData = [
                     $this->getValidToolData(['title' => 'Tool 1']), 
                     $this->getValidToolData(['title' => 'Tool 2'])
                    ];

        $response = $this->postJson("/api/tools/bulk", ['tools' => $toolsData]);
        
        $response->assertCreated()
                 ->assertJsonFragment(['title' => 'Tool 1'])
                 ->assertJsonFragment(['title' => 'Tool 2']);

        foreach ($toolsData as $toolData) {
            $this->assertDatabaseHas('tools', [
                'title' => $toolData['title'],
                'link' => $toolData['link'],
                'description' => $toolData['description'],
                'user_id' => $user->id,
            ]);
        }
    }

    public function test_bulk_store_with_invalid_data()
    {
        $this->createAuthenticatedUser();

        $toolsData = [
            [
                'title' => '',
                'link' => 'invalid-url',
                'description' => '',
                'tags' => ['tag1']
            ],
            [
                'title' => 'Tool 2',
                'link' => 'https://tool2.com',
                'description' => 'Description for Tool 2',
                'tags' => ['tag3']
            ]
        ];

        $response = $this->postJson("/api/tools/bulk", ['tools' => $toolsData]);
        
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['tools.0.title', 'tools.0.link', 'tools.0.description']);
    }

    public function test_bulk_store_with_empty_array()
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson("/api/tools/bulk", ['tools' => []]);
        
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['tools']);
    }

    public function test_show_tool_successfull()
    {        
        $user = $this->createAuthenticatedUser();

        $tool = Tool::factory()->for($user)->hasTags(2)->create();
        
        $response = $this->getJson("/api/tools/{$tool->id}");
        
        $response->assertOk()
                 ->assertJsonFragment(['title' => $tool->title])
                 ->assertJsonFragment(['link' => $tool->link])
                 ->assertJsonFragment(['description' => $tool->description])
                 ->assertJsonFragment(['tags' => $tool->tags->pluck('name')->toArray()]);
    }

    public function test_user_cannot_view_others_tool()
    {
        $user = $this->createAuthenticatedUser();

        $unauthorizedUser = User::factory()->create();
        $retrievedUser = User::find($unauthorizedUser->id);
        
        $tool = Tool::factory()->create(['user_id' => $user->id]);

        $this->actingAs($retrievedUser)
             ->getJson("/api/tools/{$tool->id}")
             ->assertNotFound();
    }

    public function test_show_tool_not_found()
    {        
        $user = $this->createAuthenticatedUser();

        $response = $this->getJson("/api/tools/9999");
        
        $response->assertNotFound()
                 ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_guests_cannot_view_tools()
    {
        $tool = Tool::factory()->create();

        $this->getJson("/api/tools/{$tool->id}")
             ->assertUnauthorized();
    }

    public function test_destroy_tool_successfull()
    {
        $user = $this->createAuthenticatedUser();

        $tool = Tool::factory()->for($user)->create();
        
        $response = $this->deleteJson("/api/tools/{$tool->id}");
        
        $response->assertOk()
                 ->assertJson([]);
        
        $this->assertDatabaseMissing('tools', ['id' => $tool->id]);
    }
    public function test_destroy_tool_not_found()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->deleteJson("/api/tools/9999");
        
        $response->assertNotFound()
                 ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_guests_cannot_delete_tools()
    {
        $tool = Tool::factory()->create();

        $this->deleteJson("/api/tools/{$tool->id}")
            ->assertUnauthorized();
    }

    public function test_user_cannot_delete_others_tool()
    {
        $user = $this->createAuthenticatedUser();

        $unauthorizedUser = User::factory()->create();
        $retrievedUser = User::find($unauthorizedUser->id);

        $tool = Tool::factory()->create(['user_id' => $user->id]);

        $this->actingAs($retrievedUser)
             ->deleteJson("/api/tools/{$tool->id}")
             ->assertNotFound();
    }

    public function test_update_tool_successfull()
    {
        $user = $this->createAuthenticatedUser();

        $tool = Tool::factory()->for($user)->create();
        
        $updatedData = $this->getValidToolData();
        
        $response = $this->putJson("/api/tools/{$tool->id}", $updatedData);
        
        $response->assertOk()
                 ->assertJsonFragment(['title' => $updatedData['title']])
                 ->assertJsonFragment(['link' => $updatedData['link']])
                 ->assertJsonFragment(['description' => $updatedData['description']]);

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'title' => $updatedData['title'],
            'link' => $updatedData['link'],
            'description' => $updatedData['description'],
        ]);
        $this->assertDatabaseHas('tags', [
            'name' => 'test-tag',
        ]);
    }

    public function test_update_tool_not_found()
    {
        $this->createAuthenticatedUser();
 
        $updatedData = $this->getValidToolData();
        
        $response = $this->putJson("/api/tools/9999", $updatedData);
        
        $response->assertNotFound()
                 ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_update_tool_with_invalid_data()
    {
        $user = $this->createAuthenticatedUser();

        $tool = Tool::factory()->for($user)->create();
        
        $updatedData = $this->getInvalidToolData();
        
        $response = $this->putJson("/api/tools/{$tool->id}", $updatedData);
        
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['title', 'link', 'description']);
    }

    public function test_guests_cannot_update_tools()
    {
        $tool = Tool::factory()->create();

        $this->putJson("/api/tools/{$tool->id}")
             ->assertUnauthorized();
    }

    public function test_user_cannot_update_others_tool()
    {
        $user = $this->createAuthenticatedUser();
        $unauthorizedUser = User::factory()->create();
        $retrievedUser = User::find($unauthorizedUser->id);

        $tool = Tool::factory()->create(['user_id' => $user->id]);

        $updatedData = $this->getValidToolData();

        $this->actingAs($retrievedUser)
             ->putJson("/api/tools/{$tool->id}", $updatedData)
             ->assertNotFound();
    }

    private function createAuthenticatedUser()
    {
        User::factory()->create();
        $user = User::first();
        $this->actingAs($user);
        return $user;
    }

    private function getValidToolData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Tool',
            'link' => 'https://test.com',
            'description' => 'Test description',
            'tags' => ['test-tag']
        ], $overrides);
    }
    private function getInvalidToolData(array $overrides = []): array
    {
        return array_merge([
            'title' => '',
            'link' => 'invalid-url',
            'description' => '',
            'tags' => ['test-tag']
        ], $overrides);
    }

}
