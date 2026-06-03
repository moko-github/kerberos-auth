<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Support;

class Kerberos
{
    /**
     * Resolve the application's User model class name.
     *
     * Resolution order:
     *   1. kerberos.user_model (explicit override)
     *   2. auth.providers.users.model (Laravel default)
     *   3. App\Models\User (last-resort fallback)
     */
    public static function userModel(): string
    {
        $model = config('kerberos.user_model')
            ?: config('auth.providers.users.model')
            ?: 'App\\Models\\User';

        return ltrim($model, '\\');
    }

    /**
     * Route name users are redirected to after a successful Kerberos login.
     */
    public static function successRoute(): string
    {
        return config('kerberos.redirects.success', 'dashboard');
    }

    /**
     * Route name used for the login / fallback redirect.
     */
    public static function loginRoute(): string
    {
        return config('kerberos.redirects.login', 'login');
    }
}
