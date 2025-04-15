<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ToolFactory extends Factory
{
    
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
            'link' =>$this->faker->url(),
            'description' => $this->faker->paragraph()
        ];
    }
}
