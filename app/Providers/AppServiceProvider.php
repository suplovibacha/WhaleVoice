<?php

namespace App\Providers;

use App\Auth\FirebaseUserProvider;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::provider('firebase', function ($app, array $config) {
            return new FirebaseUserProvider(
                $app->make(FirestoreService::class)
            );
        });
    }
}