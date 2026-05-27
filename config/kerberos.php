<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kerberos Authentication Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => env('KERBEROS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Server Variable Name
    |--------------------------------------------------------------------------
    */

    'server_variable' => env('KERBEROS_SERVER_VAR', 'REMOTE_USER'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Authentication
    |--------------------------------------------------------------------------
    */

    'fallback_auth' => env('KERBEROS_FALLBACK_AUTH', true),

    /*
    |--------------------------------------------------------------------------
    | Simulation Mode (Development Only)
    |--------------------------------------------------------------------------
    |
    | WARNING: Automatically disabled if APP_ENV=production.
    |
    */

    'simulation_mode' => env('KERBEROS_SIMULATION_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated email addresses. If empty, all Admin-role users
    | are notified.
    |
    */

    'admin_notification_emails' => array_filter(
        explode(',', env('KERBEROS_ADMIN_EMAILS', ''))
    ),

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Mode
    |--------------------------------------------------------------------------
    |
    | 'immediate' : send email for each event
    | 'disabled'  : no notifications
    |
    */

    'admin_notification_mode' => env('KERBEROS_ADMIN_NOTIFICATION_MODE', 'immediate'),

    /*
    |--------------------------------------------------------------------------
    | Automatic Cleanup Days
    |--------------------------------------------------------------------------
    */

    'auto_cleanup_attempts_days' => env('KERBEROS_AUTO_CLEANUP_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Allowed Domains (Optional)
    |--------------------------------------------------------------------------
    |
    | Whitelist of Kerberos domains. Empty = all domains accepted.
    | Example: ['example.fr', 'corp.example.fr']
    |
    */

    'allowed_domains' => array_filter(
        explode(',', env('KERBEROS_ALLOWED_DOMAINS', ''))
    ),

    /*
    |--------------------------------------------------------------------------
    | Additional Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Route names to exclude from Kerberos authentication, in addition to
    | the package defaults (access-denied, access-request.create, logout,
    | livewire.*).
    |
    | Supports wildcards: 'admin.*', 'api.*'
    |
    */

    'excluded_routes' => [],

    /*
    |--------------------------------------------------------------------------
    | Role Check Strategy
    |--------------------------------------------------------------------------
    |
    | Defines how the package determines whether a user is allowed to log in.
    | A user that fails this check receives NO_ROLE status and is redirected
    | to the access-request form.
    |
    | strategy 'column'
    |   Checks a single column on the User model using an operator.
    |   operator 'is_not_null' (default) : user is allowed if $user->{column} is not null.
    |   operator 'is_null'               : user is allowed if $user->{column} is null.
    |
    |   Examples:
    |     Single-role FK  : column = 'role_id',    operator = 'is_not_null'
    |     Soft-delete gate: column = 'deleted_at',  operator = 'is_null'
    |
    | strategy 'relation'
    |   Checks that $user->{relation}()->exists() returns true.
    |   Suitable for multi-role systems (Spatie Permission, custom pivot, etc.).
    |
    | strategy 'callable'
    |   Delegates the check to a class implementing
    |   MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface.
    |   The class is resolved via the service container (supports injection).
    |   Use for any custom / composite business logic.
    |
    */

    'role_check' => [
        'strategy' => 'column',
        'column'   => 'role_id',
        'operator' => 'is_not_null',
        'relation' => 'roles',
        'callable' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Install Seeders
    |--------------------------------------------------------------------------
    |
    | Controls which seeders are executed during `kerberos:install`.
    | These can also be overridden via command options:
    |   --no-seed   : skip all seeders
    |   --no-roles  : skip RolesSeeder only
    |
    */

    'install' => [
        'run_seeders' => true,
        'seed_roles'  => true,
    ],

];
