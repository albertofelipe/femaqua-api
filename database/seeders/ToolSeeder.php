<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Tag::factory()->count(10)->create();

        Tool::factory()
            ->count(25)
            ->create()
            ->each(function ($tool) use ($tags) {
                $tool->tags()->attach(
                   $tags->random(3)->pluck('id')->toArray()
                );
            });
    }
}
