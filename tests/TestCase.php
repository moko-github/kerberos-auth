<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Tests;

use Illuminate\Support\Facades\Route;
use Livewire\LivewireServiceProvider;
use MokoGithub\KerberosAuth\KerberosServiceProvider;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            KerberosServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('kerberos.user_model', User::class);
        $app['config']->set('kerberos.enabled', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Base users table must exist before the package adds kerberos/role_id.
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function defineRoutes($router): void
    {
        // Named routes the package redirects to (normally provided by the host app
        // and the install command).
        Route::get('/login', fn () => 'login')->name('login');
        Route::get('/dashboard', fn () => 'dashboard')->name('dashboard');
        Route::get('/demande-acces', fn () => 'request-access')->name('access-request.create');
        Route::get('/acces-refuse', fn () => 'access-denied')->name('access-denied');
    }
}
