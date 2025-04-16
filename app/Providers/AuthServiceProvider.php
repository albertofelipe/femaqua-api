<?php

namespace App\Providers;

use App\Models\Tool;
use App\Policies\ToolPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tool::class => ToolPolicy::class,
    ];
    
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
