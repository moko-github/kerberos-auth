<?php

declare(strict_types=1);

use MokoGithub\KerberosAuth\Database\Seeders\KerberosSetupSeeder;
use MokoGithub\KerberosAuth\Models\Role;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;

function makeUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'Jane',
        'email' => 'jane'.uniqid().'@example.com',
        'password' => bcrypt('secret'),
    ], $attributes));
}

describe('KerberosSetupSeeder', function () {
    it('assigns the User role to existing users without a role', function () {
        $userRole = Role::create(['name' => 'User']);
        Role::create(['name' => 'Admin']);

        $withoutRole = makeUser(['role_id' => null]);
        $withRole = makeUser(['role_id' => $userRole->id]);

        (new KerberosSetupSeeder)->run();

        expect($withoutRole->fresh()->role_id)->toBe($userRole->id)
            ->and($withRole->fresh()->role_id)->toBe($userRole->id);
    });

    it('does not assign a role when the User role does not exist', function () {
        $user = makeUser(['role_id' => null]);

        (new KerberosSetupSeeder)->run();

        expect($user->fresh()->role_id)->toBeNull();
    });

    it('creates the admin test account when it does not exist', function () {
        Role::create(['name' => 'User']);
        $adminRole = Role::create(['name' => 'Admin']);

        (new KerberosSetupSeeder)->run();

        $admin = User::where('email', 'admin@example.com')->first();

        expect($admin)->not->toBeNull()
            ->and($admin->kerberos)->toBe('admin@krb.example.com')
            ->and($admin->role_id)->toBe($adminRole->id);
    });

    it('always assigns the Admin role to an existing admin@example.com', function () {
        Role::create(['name' => 'User']);
        $adminRole = Role::create(['name' => 'Admin']);

        makeUser(['email' => 'admin@example.com', 'role_id' => null]);

        (new KerberosSetupSeeder)->run();

        expect(User::where('email', 'admin@example.com')->first()->role_id)
            ->toBe($adminRole->id);
    });

    it('does nothing in production', function () {
        app()->detectEnvironment(fn () => 'production');

        Role::create(['name' => 'User']);
        Role::create(['name' => 'Admin']);

        (new KerberosSetupSeeder)->run();

        expect(User::where('email', 'admin@example.com')->count())->toBe(0);

        app()->detectEnvironment(fn () => 'testing');
    });

    it('is idempotent when run multiple times', function () {
        Role::create(['name' => 'User']);
        Role::create(['name' => 'Admin']);

        (new KerberosSetupSeeder)->run();
        (new KerberosSetupSeeder)->run();

        expect(User::where('email', 'admin@example.com')->count())->toBe(1);
    });
});
