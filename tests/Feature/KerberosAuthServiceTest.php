<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Notification;
use MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface;
use MokoGithub\KerberosAuth\DTOs\AuthResult;
use MokoGithub\KerberosAuth\Models\KerberosAttempt;
use MokoGithub\KerberosAuth\Models\Role;
use MokoGithub\KerberosAuth\Notifications\UnknownKerberosAttemptNotification;
use MokoGithub\KerberosAuth\Services\KerberosAuthService;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;

beforeEach(function () {
    $this->service = app(KerberosAuthService::class);
    request()->server->remove('REMOTE_USER');
});

function user(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'User',
        'email' => uniqid().'@example.com',
        'password' => bcrypt('secret'),
    ], $attributes));
}

function kerberos(string $identifier): void
{
    request()->server->set('REMOTE_USER', $identifier);
}

it('returns no-kerberos when disabled and no simulation', function () {
    config()->set('kerberos.enabled', false);

    expect($this->service->authenticate()->status)->toBe(AuthResult::NO_KERBEROS);
});

it('returns no-kerberos when the identifier is empty', function () {
    expect($this->service->authenticate()->status)->toBe(AuthResult::NO_KERBEROS);
});

it('returns unknown-user, logs the attempt and notifies admins', function () {
    Notification::fake();

    $admin = user(['kerberos' => 'admin@krb']);
    $adminRole = Role::create(['name' => 'Admin']);
    $admin->update(['role_id' => $adminRole->id]);

    kerberos('ghost@krb');

    $result = $this->service->authenticate();

    expect($result->status)->toBe(AuthResult::UNKNOWN_USER)
        ->and($result->kerberos)->toBe('ghost@krb');

    expect(KerberosAttempt::where('result', 'unknown_user')->count())->toBe(1);

    Notification::assertSentTo($admin, UnknownKerberosAttemptNotification::class);
});

it('notifies configured admin emails on-demand instead of admin users', function () {
    Notification::fake();

    config()->set('kerberos.admin_notification_emails', ['rssi@example.com', 'it@example.com']);

    kerberos('ghost@krb');

    $this->service->authenticate();

    Notification::assertSentOnDemand(
        UnknownKerberosAttemptNotification::class,
        function ($notification, $channels, $notifiable) {
            return in_array('rssi@example.com', (array) $notifiable->routes['mail'], true);
        }
    );
});

it('respects the configurable admin role when resolving recipients', function () {
    Notification::fake();

    $role = Role::create(['name' => 'Superviseur']);
    $supervisor = user(['kerberos' => 'sup@krb', 'role_id' => $role->id]);
    config()->set('kerberos.admin_role', 'Superviseur');

    kerberos('ghost@krb');

    $this->service->authenticate();

    Notification::assertSentTo($supervisor, UnknownKerberosAttemptNotification::class);
});

it('returns no-role for a known user without a role', function () {
    user(['kerberos' => 'bob@krb', 'role_id' => null]);

    kerberos('bob@krb');

    $result = $this->service->authenticate();

    expect($result->status)->toBe(AuthResult::NO_ROLE)
        ->and($result->user->kerberos)->toBe('bob@krb');
    expect(KerberosAttempt::where('result', 'no_role')->count())->toBe(1);
});

it('returns success for a known user with a role', function () {
    $role = Role::create(['name' => 'User']);
    $alice = user(['kerberos' => 'alice@krb', 'role_id' => $role->id]);

    kerberos('alice@krb');

    $result = $this->service->authenticate();

    expect($result->status)->toBe(AuthResult::SUCCESS);

    $attempt = KerberosAttempt::where('result', 'success')->first();
    expect($attempt)->not->toBeNull()
        ->and($attempt->user_id)->toBe($alice->id);
});

it('stores user_id on the attempt for no-role', function () {
    $bob = user(['kerberos' => 'bob2@krb', 'role_id' => null]);

    kerberos('bob2@krb');

    $this->service->authenticate();

    $attempt = KerberosAttempt::where('result', 'no_role')->first();
    expect($attempt->user_id)->toBe($bob->id);
});

it('leaves user_id null on the attempt for unknown users', function () {
    kerberos('nobody@krb');

    $this->service->authenticate();

    $attempt = KerberosAttempt::where('result', 'unknown_user')->first();
    expect($attempt->user_id)->toBeNull();
});

describe('role_check strategies', function () {
    it('column / is_not_null grants access when the column is set', function () {
        $role = Role::create(['name' => 'User']);
        user(['kerberos' => 'c@krb', 'role_id' => $role->id]);
        kerberos('c@krb');

        expect($this->service->authenticate()->status)->toBe(AuthResult::SUCCESS);
    });

    it('relation strategy grants access when the relation exists', function () {
        config()->set('kerberos.role_check.strategy', 'relation');
        config()->set('kerberos.role_check.relation', 'role');

        $role = Role::create(['name' => 'User']);
        user(['kerberos' => 'rel@krb', 'role_id' => $role->id]);
        kerberos('rel@krb');

        expect($this->service->authenticate()->status)->toBe(AuthResult::SUCCESS);
    });

    it('relation strategy denies access when the relation is empty', function () {
        config()->set('kerberos.role_check.strategy', 'relation');
        config()->set('kerberos.role_check.relation', 'role');

        user(['kerberos' => 'norel@krb', 'role_id' => null]);
        kerberos('norel@krb');

        expect($this->service->authenticate()->status)->toBe(AuthResult::NO_ROLE);
    });

    it('callable strategy delegates to the configured class', function () {
        config()->set('kerberos.role_check.strategy', 'callable');
        config()->set('kerberos.role_check.callable', AlwaysDeny::class);

        user(['kerberos' => 'cb@krb']);
        kerberos('cb@krb');

        expect($this->service->authenticate()->status)->toBe(AuthResult::NO_ROLE);
    });

    it('callable strategy throws when no class is configured', function () {
        config()->set('kerberos.role_check.strategy', 'callable');
        config()->set('kerberos.role_check.callable', null);

        user(['kerberos' => 'cb2@krb']);
        kerberos('cb2@krb');

        $this->service->authenticate();
    })->throws(RuntimeException::class);
});

class AlwaysDeny implements UserAccessCheckInterface
{
    public function check(Authenticatable $user): bool
    {
        return false;
    }
}
