<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ApiRepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Api\User\UserRepositoryInterface',
            'App\Repositories\Api\User\UserRepository'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
