<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use MokoGithub\KerberosAuth\Database\Seeders\KerberosSetupSeeder;
use MokoGithub\KerberosAuth\Database\Seeders\RolesSeeder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;

class KerberosInstallCommand extends Command
{
    protected $signature = 'kerberos:install
                            {--no-seed : Skip all seeders without prompt}
                            {--no-roles : Skip the roles system (migration + seeder) without prompt}';

    protected $description = "Installe l'authentification Kerberos SSO dans l'application Laravel";

    public function handle(): int
    {
        intro("Installation de l'authentification Kerberos...");

        $withRoles = $this->shouldInstallRoles();

        $this->configureMiddleware();
        $this->configureUserModel($withRoles);
        $this->configureRoutes();
        $this->configureScheduler();
        $this->appendEnvVariables();
        $this->runKerberosMigrations($withRoles);
        $this->runSeeders($withRoles);

        $this->info('✓ Authentification Kerberos installée avec succès.');

        note(
            "Prochaines étapes :\n".
            "  1. Définissez KERBEROS_ENABLED=true dans votre fichier .env\n".
            "  2. Configurez KERBEROS_ADMIN_EMAILS avec les adresses email des administrateurs\n".
            "  3. Configurez votre serveur web (Apache/Nginx) avec le module Kerberos\n".
            '  4. Pour les tests en local, définissez KERBEROS_SIMULATION_MODE=true'
        );

        outro('Installation terminée !');

        return self::SUCCESS;
    }

    protected function shouldInstallRoles(): bool
    {
        if ($this->option('no-roles')) {
            return false;
        }

        if (! config('kerberos.install.seed_roles', true)) {
            return false;
        }

        return confirm(
            'Installer le système de rôles ? (table roles + colonne role_id sur users)',
            default: true
        );
    }

    protected function configureMiddleware(): void
    {
        $appFile = base_path('bootstrap/app.php');
        $content = File::get($appFile);

        if (str_contains($content, 'KerberosAuthentication')) {
            $this->line('  Middleware déjà enregistré, ignoré.');

            return;
        }

        $inject = "\n        \$middleware->appendToGroup('web', \\MokoGithub\\KerberosAuth\\Http\\Middleware\\KerberosAuthentication::class);";
        $inject .= "\n        \$middleware->alias(['kerberos.simulation' => \\MokoGithub\\KerberosAuth\\Http\\Middleware\\EnsureKerberosSimulationAllowed::class]);";

        $new = preg_replace(
            '/(->withMiddleware\(function\s*\(Middleware\s*\$middleware\)[^{]*\{)/',
            '$1'.$inject,
            $content,
            1
        );

        if ($new === null || $new === $content) {
            $this->warn("⚠  Impossible d'injecter le middleware automatiquement.");
            $this->warn('   Ajoutez manuellement dans bootstrap/app.php, dans ->withMiddleware(...) :');
            $this->line("       \$middleware->appendToGroup('web', \\MokoGithub\\KerberosAuth\\Http\\Middleware\\KerberosAuthentication::class);");
            $this->line("       \$middleware->alias(['kerberos.simulation' => \\MokoGithub\\KerberosAuth\\Http\\Middleware\\EnsureKerberosSimulationAllowed::class]);");

            return;
        }

        File::put($appFile, $new);
        $this->line('  Middleware enregistré.');
    }

    protected function configureUserModel(bool $withRoles): void
    {
        $userFile = base_path('app/Models/User.php');
        $content = File::get($userFile);

        if (! str_contains($content, "'kerberos'")) {
            $fillableAddition = $withRoles ? "'kerberos',\n        'role_id'," : "'kerberos',";

            $new = preg_replace(
                "/'password',(\s*\];)/",
                "'password',\n        {$fillableAddition}$1",
                $content,
                1
            );

            if ($new === null || $new === $content) {
                $this->warn("⚠  Impossible d'ajouter kerberos dans \$fillable.");
                $this->warn('   Ajoutez manuellement dans app/Models/User.php :');
                $hint = $withRoles ? "'kerberos', 'role_id'" : "'kerberos'";
                $this->line("       protected \$fillable = [..., {$hint}];");
            } else {
                $content = $new;
                $added = $withRoles ? 'kerberos et role_id' : 'kerberos';
                $this->line("  Champ(s) {$added} ajouté(s) au modèle User.");
            }
        } else {
            $this->line('  Champ kerberos déjà présent, ignoré.');
        }

        if ($withRoles && ! str_contains($content, 'function role()')) {
            $roleMethod = "\n    public function role(): \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo\n    {\n        return \$this->belongsTo(\\MokoGithub\\KerberosAuth\\Models\\Role::class);\n    }\n";

            $lastBrace = strrpos($content, '}');
            $content = substr($content, 0, $lastBrace).$roleMethod.'}'.substr($content, $lastBrace + 1);
            $this->line('  Relation role() ajoutée au modèle User.');
        } elseif ($withRoles) {
            $this->line('  Relation role() déjà présente, ignorée.');
        }

        File::put($userFile, $content);
    }

    protected function configureRoutes(): void
    {
        $routesFile = base_path('routes/web.php');
        $routesContent = File::get($routesFile);

        if (str_contains($routesContent, 'access-request.create')) {
            $this->line('  Routes Kerberos déjà présentes, ignorées.');

            return;
        }

        $kerberosRoutes = "\n\n// Routes d'authentification Kerberos\nRoute::middleware('guest')->group(function (): void {\n    Route::get('/demande-acces', \\MokoGithub\\KerberosAuth\\Livewire\\Auth\\RequestAccess::class)->name('access-request.create');\n    Route::get('/acces-refuse', \\MokoGithub\\KerberosAuth\\Livewire\\Auth\\AccessDenied::class)->name('access-denied');\n});\n";

        File::append($routesFile, $kerberosRoutes);
        $this->line('  Routes Kerberos ajoutées.');
    }

    protected function configureScheduler(): void
    {
        $consoleFile = base_path('routes/console.php');
        $consoleContent = File::get($consoleFile);

        if (str_contains($consoleContent, 'kerberos:purge-attempts')) {
            $this->line('  Scheduler déjà configuré, ignoré.');

            return;
        }

        File::append($consoleFile, "\n\\Illuminate\\Support\\Facades\\Schedule::command('kerberos:purge-attempts')->dailyAt('03:00');\n");
        $this->line('  Scheduler configuré.');
    }

    protected function appendEnvVariables(): void
    {
        $envFile = base_path('.env');

        if (! File::exists($envFile)) {
            return;
        }

        $content = File::get($envFile);

        if (str_contains($content, 'KERBEROS_ENABLED')) {
            $this->line('  Variables .env déjà présentes, ignorées.');

            return;
        }

        $envBlock = "\n# Kerberos Authentication\n".
            "KERBEROS_ENABLED=false\n".
            "KERBEROS_SERVER_VAR=REMOTE_USER\n".
            "KERBEROS_FALLBACK_AUTH=true\n".
            "KERBEROS_SIMULATION_MODE=false\n".
            "KERBEROS_ADMIN_EMAILS=\n".
            "KERBEROS_ADMIN_NOTIFICATION_MODE=immediate\n".
            "KERBEROS_AUTO_CLEANUP_DAYS=30\n".
            "KERBEROS_ALLOWED_DOMAINS=\n";

        File::append($envFile, $envBlock);
        $this->line('  Variables .env ajoutées.');
    }

    protected function runKerberosMigrations(bool $withRoles): void
    {
        $migrationsPath = realpath(__DIR__.'/../../../database/migrations');

        if ($withRoles) {
            $this->call('migrate', [
                '--path' => $migrationsPath.'/2025_11_18_100000_create_roles_table.php',
                '--realpath' => true,
                '--force' => true,
            ]);
        }

        foreach ([
            '2025_11_18_100001_add_kerberos_columns_to_users_table.php',
            '2025_11_18_100002_create_kerberos_attempts_table.php',
            '2025_11_18_100003_create_access_requests_table.php',
        ] as $file) {
            $this->call('migrate', [
                '--path' => $migrationsPath.'/'.$file,
                '--realpath' => true,
                '--force' => true,
            ]);
        }
    }

    protected function runSeeders(bool $withRoles): void
    {
        if ($this->option('no-seed') || ! config('kerberos.install.run_seeders', true)) {
            return;
        }

        if (! confirm('Exécuter les seeders Kerberos ?', default: true)) {
            return;
        }

        if ($withRoles) {
            $this->call('db:seed', ['--class' => RolesSeeder::class, '--force' => true]);
        }

        $this->call('db:seed', ['--class' => KerberosSetupSeeder::class, '--force' => true]);
    }
}
