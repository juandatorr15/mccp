<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Opcodes\LogViewer\Facades\LogViewer;


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
        //
        if (app()->environment('production')) {
            URL::forceScheme('https');
            LogViewer::auth(function ($request) {
                return true;
            });
        }
    }
}
