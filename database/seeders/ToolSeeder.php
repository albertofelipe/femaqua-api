<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Tag::factory()->count(10)->create();
        $user = User::first();

        Tool::factory()
            ->count(25)
            ->for($user)
            ->create()
            ->each(function ($tool) use ($tags) {
                $tool->tags()->attach(
                   $tags->random(3)->pluck('id')->toArray()
                );
            });
    }
}
