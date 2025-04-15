<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        $tags = ['php', 'node', 'docker', 'jenkins', 'typescript', 'react', 'laravel', 'java', 'spring', 'mysql'];

        static $index = 0;
        $name = $tags[$index];
        $index++;

        return [
            'name' => $name
        ];
    }
}
