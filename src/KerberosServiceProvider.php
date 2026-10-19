<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use MokoGithub\KerberosAuth\Console\Commands\KerberosInstallCommand;
use MokoGithub\KerberosAuth\Console\Commands\PurgeKerberosAttempts;
use MokoGithub\KerberosAuth\Livewire\Auth\AccessDenied;
use MokoGithub\KerberosAuth\Livewire\Auth\RequestAccess;
use MokoGithub\KerberosAuth\Livewire\Auth\SimulateKerberos;
use MokoGithub\KerberosAuth\Livewire\Auth\SimulationBanner;
use MokoGithub\KerberosAuth\Support\Kerberos;

class KerberosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kerberos.php', 'kerberos');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->migrationPaths());
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kerberos-auth');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'kerberos-auth');

        Livewire::component('auth.access-denied', AccessDenied::class);
        Livewire::component('auth.request-access', RequestAccess::class);
        Livewire::component('auth.simulate-kerberos', SimulateKerberos::class);
        Livewire::component('auth.simulation-banner', SimulationBanner::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kerberos.php' => config_path('kerberos.php'),
            ], 'kerberos-config');

            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'kerberos-seeders');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/kerberos-auth'),
            ], 'kerberos-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/kerberos-auth'),
            ], 'kerberos-lang');

            $this->commands([
                KerberosInstallCommand::class,
                PurgeKerberosAttempts::class,
            ]);
        }
    }

    /**
     * Package migration files to register with the migrator.
     *
     * The roles migration is left out when the roles system is disabled, so a
     * later `php artisan migrate` does not create the roles table / role_id.
     *
     * @return array<int, string>
     */
    protected function migrationPaths(): array
    {
        $files = glob(__DIR__.'/../database/migrations/*.php') ?: [];

        if (! Kerberos::rolesEnabled()) {
            $files = array_filter(
                $files,
                fn (string $file) => ! str_ends_with($file, '_create_roles_table.php')
            );
        }

        return array_values($files);
    }
}
