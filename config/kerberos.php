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
    | User Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model representing your application's users.
    |
    | null (default) : resolved automatically from
    |                  config('auth.providers.users.model'), then falls back
    |                  to App\Models\User.
    | string         : explicit class name, e.g. \App\Models\Account::class.
    |
    */

    'user_model' => env('KERBEROS_USER_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Redirect Routes
    |--------------------------------------------------------------------------
    |
    | Route names used by the package for its redirects.
    |
    | success : where the user is sent after a successful Kerberos login.
    | login   : login / fallback route (access denied, simulation disable,
    |           access-request submission, etc.).
    |
    */

    'redirects' => [
        'success' => env('KERBEROS_SUCCESS_ROUTE', 'dashboard'),
        'login'   => env('KERBEROS_LOGIN_ROUTE', 'login'),
    ],

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
    |
    | Controls what happens when Kerberos is enabled but no identifier is
    | provided (user without a ticket, off-network, etc.).
    |
    | true  : let the request through so the user reaches the standard login
    |         form (classic email/password fallback).
    | false : strict Kerberos — no ticket means no access. The request is
    |         aborted with a 403.
    |
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
    | Admin Role Name
    |--------------------------------------------------------------------------
    |
    | Name of the role considered "administrator" when resolving notification
    | recipients from the database (used only when admin_notification_emails
    | is empty). Assumes the package's column/relation role model.
    |
    | For 'relation' / 'callable' role-check strategies (Spatie, custom), set
    | admin_notification_emails explicitly instead.
    |
    */

    'admin_role' => env('KERBEROS_ADMIN_ROLE', 'Admin'),

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated email addresses that should receive admin notifications
    | (new access request, unknown Kerberos attempt).
    |
    | If empty   : all users holding the admin role (see admin_role) are notified.
    | If provided: these addresses are notified directly (on-demand mail),
    |              whether or not they correspond to a User record.
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
    | Page Layout
    |--------------------------------------------------------------------------
    |
    | Layout used by the package's Livewire page components (access-denied,
    | request-access).
    |
    | null (default) : uses the package's own minimal guest layout
    |                  (kerberos-auth::layouts.guest), which only requires
    |                  Tailwind CSS. Publishable via --tag=kerberos-views.
    |
    | string         : use any layout from the application.
    |                  Example: 'layouts.auth', 'components.layouts.app'
    |
    */

    'layout' => null,

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
