<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit route model binding for member enrollment routes and class content routes
        Route::bind('class', function ($value, $route) {
            return \App\Models\DiscipleshipClass::findOrFail($value);
        });

        // Route model binding for class content
        Route::bind('content', function ($value, $route) {
            return \App\Models\ClassContent::findOrFail($value);
        });
    }
}
