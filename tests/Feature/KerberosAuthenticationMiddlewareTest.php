<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use MokoGithub\KerberosAuth\Http\Middleware\KerberosAuthentication;
use MokoGithub\KerberosAuth\Models\Role;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;

beforeEach(function () {
    Notification::fake();
    unset($_SERVER['REMOTE_USER']);

    Route::middleware(['web', KerberosAuthentication::class])
        ->get('/protected', fn () => 'protected')
        ->name('protected');
});

afterEach(function () {
    unset($_SERVER['REMOTE_USER']);
});

it('lets the request through when Kerberos is disabled', function () {
    config()->set('kerberos.enabled', false);

    $this->get('/protected')->assertOk()->assertSee('protected');
});

it('logs a user in and redirects to the success route on success', function () {
    $role = Role::create(['name' => 'User']);
    User::create([
        'name' => 'A', 'email' => 'a@example.com', 'password' => bcrypt('x'),
        'kerberos' => 'alice@krb', 'role_id' => $role->id,
    ]);

    $_SERVER['REMOTE_USER'] = 'alice@krb';

    $this->get('/protected')->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

it('redirects a roleless user to the access-request form', function () {
    User::create([
        'name' => 'B', 'email' => 'b@example.com', 'password' => bcrypt('x'),
        'kerberos' => 'bob@krb', 'role_id' => null,
    ]);

    $_SERVER['REMOTE_USER'] = 'bob@krb';

    $this->get('/protected')->assertRedirect(route('access-request.create'));
    $this->assertGuest();
});

it('redirects an unknown identifier to the access-denied page', function () {
    $_SERVER['REMOTE_USER'] = 'ghost@krb';

    $this->get('/protected')->assertRedirect(route('access-denied'));
    $this->assertGuest();
});

it('falls through to the login form when fallback_auth is enabled and no ticket', function () {
    config()->set('kerberos.fallback_auth', true);

    $this->get('/protected')->assertOk()->assertSee('protected');
});

it('aborts with 403 when fallback_auth is disabled and no ticket', function () {
    config()->set('kerberos.fallback_auth', false);

    $this->get('/protected')->assertForbidden();
});
