<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use MokoGithub\KerberosAuth\DTOs\AuthResult;
use MokoGithub\KerberosAuth\Models\KerberosAttempt;
use MokoGithub\KerberosAuth\Notifications\UnknownKerberosAttemptNotification;
use MokoGithub\KerberosAuth\Services\KerberosAuthService;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;

beforeEach(function () {
    $this->service = app(KerberosAuthService::class);
    unset($_SERVER['REMOTE_USER']);
});

afterEach(function () {
    unset($_SERVER['REMOTE_USER']);
});

function user(array $attributes = []): User
{
    return User::create(array_merge([
        'name'     => 'User',
        'email'    => uniqid().'@example.com',
        'password' => bcrypt('secret'),
    ], $attributes));
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
    $adminRole = \MokoGithub\KerberosAuth\Models\Role::create(['name' => 'Admin']);
    $admin->update(['role_id' => $adminRole->id]);

    $_SERVER['REMOTE_USER'] = 'ghost@krb';

    $result = $this->service->authenticate();

    expect($result->status)->toBe(AuthResult::UNKNOWN_USER)
        ->and($result->kerberos)->toBe('ghost@krb');

    expect(KerberosAttempt::where('result', 'unknown_user')->count())->toBe(1);

    Notification::assertSentTo($admin, UnknownKerberosAttemptNotification::class);
});

it('returns no-role for a known user without a role', function () {
    user(['kerberos' => 'bob@krb', 'role_id' => null]);

    $_SERVER['REMOTE_USER'] = 'bob@krb';

    $result = $this->service->authenticate();

    expect($result->status)->toBe(AuthResult::NO_ROLE)
        ->and($result->user->kerberos)->toBe('bob@krb');
    expect(KerberosAttempt::where('result', 'no_role')->count())->toBe(1);
});

it('returns success for a known user with a role', function () {
    $role = \MokoGithub\KerberosAuth\Models\Role::create(['name' => 'User']);
    user(['kerberos' => 'alice@krb', 'role_id' => $role->id]);

    $_SERVER['REMOTE_USER'] = 'alice@krb';

    $result = $this->service->authenticate();

    expect($result->status)->toBe(AuthResult::SUCCESS);
    expect(KerberosAttempt::where('result', 'success')->count())->toBe(1);
});

describe('role_check strategies', function () {
    it('column / is_not_null grants access when the column is set', function () {
        $role = \MokoGithub\KerberosAuth\Models\Role::create(['name' => 'User']);
        user(['kerberos' => 'c@krb', 'role_id' => $role->id]);
        $_SERVER['REMOTE_USER'] = 'c@krb';

        expect($this->service->authenticate()->status)->toBe(AuthResult::SUCCESS);
    });

    it('relation strategy grants access when the relation exists', function () {
        config()->set('kerberos.role_check.strategy', 'relation');
        config()->set('kerberos.role_check.relation', 'role');

        $role = \MokoGithub\KerberosAuth\Models\Role::create(['name' => 'User']);
        user(['kerberos' => 'rel@krb', 'role_id' => $role->id]);
        $_SERVER['REMOTE_USER'] = 'rel@krb';

        expect($this->service->authenticate()->status)->toBe(AuthResult::SUCCESS);
    });

    it('relation strategy denies access when the relation is empty', function () {
        config()->set('kerberos.role_check.strategy', 'relation');
        config()->set('kerberos.role_check.relation', 'role');

        user(['kerberos' => 'norel@krb', 'role_id' => null]);
        $_SERVER['REMOTE_USER'] = 'norel@krb';

        expect($this->service->authenticate()->status)->toBe(AuthResult::NO_ROLE);
    });

    it('callable strategy delegates to the configured class', function () {
        config()->set('kerberos.role_check.strategy', 'callable');
        config()->set('kerberos.role_check.callable', AlwaysDeny::class);

        user(['kerberos' => 'cb@krb']);
        $_SERVER['REMOTE_USER'] = 'cb@krb';

        expect($this->service->authenticate()->status)->toBe(AuthResult::NO_ROLE);
    });

    it('callable strategy throws when no class is configured', function () {
        config()->set('kerberos.role_check.strategy', 'callable');
        config()->set('kerberos.role_check.callable', null);

        user(['kerberos' => 'cb2@krb']);
        $_SERVER['REMOTE_USER'] = 'cb2@krb';

        $this->service->authenticate();
    })->throws(RuntimeException::class);
});

class AlwaysDeny implements \MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface
{
    public function check(\Illuminate\Contracts\Auth\Authenticatable $user): bool
    {
        return false;
    }
}
