<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use MokoGithub\KerberosAuth\Models\AccessRequest;
use MokoGithub\KerberosAuth\Models\Role;
use MokoGithub\KerberosAuth\Notifications\AccessRequestAcceptedNotification;
use MokoGithub\KerberosAuth\Notifications\AccessRequestRejectedNotification;
use MokoGithub\KerberosAuth\Services\AccessRequestService;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;

function admin(): User
{
    return User::create([
        'name'     => 'Admin',
        'email'    => 'admin'.uniqid().'@example.com',
        'password' => bcrypt('secret'),
    ]);
}

beforeEach(function () {
    $this->service = app(AccessRequestService::class);
    Notification::fake();
});

it('approves a request and assigns the role to an existing user', function () {
    $role = Role::create(['name' => 'User']);
    $user = User::create([
        'name' => 'Bob', 'email' => 'bob@example.com', 'password' => bcrypt('x'), 'kerberos' => 'bob@krb',
    ]);
    $request = AccessRequest::create([
        'user_id' => $user->id, 'kerberos' => 'bob@krb', 'justification' => str_repeat('a', 25), 'status' => 'pending',
    ]);

    $result = $this->service->approve($request, $role->id, 'Bienvenue', admin());

    expect($result->status)->toBe('approved')
        ->and($user->fresh()->role_id)->toBe($role->id)
        ->and($result->admin_message)->toBe('Bienvenue');

    Notification::assertSentTo($user, AccessRequestAcceptedNotification::class);
});

it('approves a request and creates the user when none exists', function () {
    $role = Role::create(['name' => 'User']);
    $request = AccessRequest::create([
        'user_id' => null, 'kerberos' => 'new@krb', 'justification' => str_repeat('a', 25), 'status' => 'pending',
    ]);

    $this->service->approve($request, $role->id, null, admin());

    $created = User::where('kerberos', 'new@krb')->first();

    expect($created)->not->toBeNull()
        ->and($created->role_id)->toBe($role->id)
        ->and($request->fresh()->user_id)->toBe($created->id);
});

it('rejects a request and notifies the user', function () {
    $user = User::create([
        'name' => 'Cara', 'email' => 'cara@example.com', 'password' => bcrypt('x'), 'kerberos' => 'cara@krb',
    ]);
    $request = AccessRequest::create([
        'user_id' => $user->id, 'kerberos' => 'cara@krb', 'justification' => str_repeat('a', 25), 'status' => 'pending',
    ]);

    $result = $this->service->reject($request, 'Profil incomplet', admin());

    expect($result->status)->toBe('rejected')
        ->and($result->admin_message)->toBe('Profil incomplet');

    Notification::assertSentTo($user, AccessRequestRejectedNotification::class);
});

it('counts pending requests', function () {
    AccessRequest::create(['kerberos' => 'a@krb', 'justification' => str_repeat('a', 25), 'status' => 'pending']);
    AccessRequest::create(['kerberos' => 'b@krb', 'justification' => str_repeat('a', 25), 'status' => 'approved']);

    expect($this->service->getPendingCount())->toBe(1);
});
