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
    | Defines how the package determines whether a user has a role assigned.
    |
    | strategy 'column'   : checks that $user->{column} is not null.
    |                       Suitable for single-role systems (default).
    |
    | strategy 'relation' : checks that $user->{relation}()->exists().
    |                       Suitable for multi-role systems (Spatie Permission,
    |                       custom pivot tables, etc.).
    |
    */

    'role_check' => [
        'strategy' => 'column',
        'column'   => 'role_id',
        'relation' => 'roles',
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
        'run_seeders'  => true,
        'seed_roles'   => true,
    ],

];
