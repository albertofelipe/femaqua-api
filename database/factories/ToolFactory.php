<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ToolFactory extends Factory
{
    
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
            'link' =>$this->faker->url(),
            'description' => $this->faker->paragraph(),
            'user_id' => User::factory(),
        ];
    }
}
