<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\UserContext;
use Illuminate\Support\ServiceProvider;

class ContextServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->scoped(UserContext::class, function () {
            return new UserContext;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
