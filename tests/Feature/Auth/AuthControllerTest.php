<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{

    use RefreshDatabase;

    public function test_register_successfull(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password')
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => $userData['name'],
                     'email' => $userData['email'],
                     'message' => 'User registered successfully'
                 ]);
    }

    public function test_register_with_invalid_data()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'p'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_successfull()
    {
        $email = fake()->unique()->safeEmail();
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'message' => 'User logged successfully'
                 ]);
    }

    public function test_login_with_invalid_credentials()
    { 
        User::factory()->create([
            'email' => $email = fake()->unique()->safeEmail(),
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(401)
                 ->assertJsonFragment([
                     'error' => 'Invalid Credentials'
                 ]);
    }

    public function test_logout_successfull()
    {
        $user = User::factory()->create([
            'email' => $email = fake()->unique()->safeEmail(),
            'password' => bcrypt('password')
        ]);
    
        $token = JWTAuth::fromUser($user);
    
        $user_retrivied = User::where('email', $email)->first();
        $this->actingAs($user_retrivied, 'api');
    
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
    
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_logout_unauthenticated()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_me_successfull()
    {
        $user = User::factory()->create([
            'email' => $email = fake()->unique()->safeEmail(),
            'password' => bcrypt('password')
        ]);
    
        $token = JWTAuth::fromUser($user);
    
        $user_retrivied = User::where('email', $email)->first();
        $this->actingAs($user_retrivied, 'api');
    
        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token
        ]);
    
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => $user->name,
                     'email' => $user->email
                 ]);
    }

    public function test_me_unauthenticated()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
