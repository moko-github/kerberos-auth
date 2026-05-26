<?php

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
                            {--no-roles : Skip RolesSeeder without prompt}';

    protected $description = "Installe l'authentification Kerberos SSO dans l'application Laravel";

    public function handle(): int
    {
        intro("Installation de l'authentification Kerberos...");

        $this->configureMiddleware();
        $this->configureUserModel();
        $this->configureRoutes();
        $this->configureScheduler();
        $this->appendEnvVariables();
        $this->runKerberosMigrations();

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

    protected function configureMiddleware(): void
    {
        $appFile = base_path('bootstrap/app.php');
        $content = File::get($appFile);

        if (str_contains($content, 'KerberosAuthentication')) {
            $this->line('  Middleware déjà enregistré, ignoré.');
            return;
        }

        $inject  = "\n        \$middleware->appendToGroup('web', \\MokoGithub\\KerberosAuth\\Http\\Middleware\\KerberosAuthentication::class);";
        $inject .= "\n        \$middleware->alias(['kerberos.simulation' => \\MokoGithub\\KerberosAuth\\Http\\Middleware\\EnsureKerberosSimulationAllowed::class]);";

        $new = preg_replace(
            '/(->withMiddleware\(function\s*\(Middleware\s*\$middleware\)[^{]*\{)/',
            '$1'.$inject,
            $content,
            1
        );

        if ($new === null || $new === $content) {
            $this->warn("⚠  Impossible d'injecter le middleware automatiquement.");
            $this->warn('   Ajoutez manuellement dans bootstrap/app.php, dans ->withMiddleware(...) :');
            $this->line("       \$middleware->appendToGroup('web', \\MokoGithub\\KerberosAuth\\Http\\Middleware\\KerberosAuthentication::class);");
            $this->line("       \$middleware->alias(['kerberos.simulation' => \\MokoGithub\\KerberosAuth\\Http\\Middleware\\EnsureKerberosSimulationAllowed::class]);");
            return;
        }

        File::put($appFile, $new);
        $this->line('  Middleware enregistré.');
    }

    protected function configureUserModel(): void
    {
        $userFile = base_path('app/Models/User.php');
        $content  = File::get($userFile);

        if (! str_contains($content, "'kerberos'")) {
            $new = preg_replace(
                "/'password',(\s*\];)/",
                "'password',\n        'kerberos',\n        'role_id',$1",
                $content,
                1
            );

            if ($new === null || $new === $content) {
                $this->warn("⚠  Impossible d'ajouter kerberos/role_id dans \$fillable.");
                $this->warn('   Ajoutez manuellement dans app/Models/User.php :');
                $this->line("       protected \$fillable = [..., 'kerberos', 'role_id'];");
            } else {
                $content = $new;
                $this->line('  Champs kerberos et role_id ajoutés au modèle User.');
            }
        } else {
            $this->line('  Champs kerberos/role_id déjà présents, ignorés.');
        }

        if (! str_contains($content, 'function role()')) {
            $roleMethod = "\n    public function role(): \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo\n    {\n        return \$this->belongsTo(\\MokoGithub\\KerberosAuth\\Models\\Role::class);\n    }\n";

            $lastBrace = strrpos($content, '}');
            $content   = substr($content, 0, $lastBrace).$roleMethod.'}'.substr($content, $lastBrace + 1);
            $this->line('  Relation role() ajoutée au modèle User.');
        } else {
            $this->line('  Relation role() déjà présente, ignorée.');
        }

        File::put($userFile, $content);
    }

    protected function configureRoutes(): void
    {
        $routesFile     = base_path('routes/web.php');
        $routesContent  = File::get($routesFile);

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
        $consoleFile    = base_path('routes/console.php');
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

    protected function runKerberosMigrations(): void
    {
        $this->call('migrate', ['--force' => true]);

        if ($this->option('no-seed') || ! config('kerberos.install.run_seeders', true)) {
            return;
        }

        if (! confirm('Exécuter les seeders Kerberos ?', default: true)) {
            return;
        }

        $skipRoles = $this->option('no-roles')
            || ! config('kerberos.install.seed_roles', true)
            || ! confirm('Inclure le RolesSeeder (crée les rôles Admin et User) ?', default: true);

        if (! $skipRoles) {
            $this->call('db:seed', ['--class' => RolesSeeder::class, '--force' => true]);
        }

        $this->call('db:seed', ['--class' => KerberosSetupSeeder::class, '--force' => true]);
    }
}
