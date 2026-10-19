<?php

declare(strict_types=1);

use MokoGithub\KerberosAuth\KerberosServiceProvider;

function registeredMigrations(): array
{
    $provider = new KerberosServiceProvider(app());

    return array_map('basename', (fn () => $this->migrationPaths())->call($provider));
}

it('registers the roles migration when the roles system is enabled', function () {
    config(['kerberos.roles_enabled' => true]);

    expect(registeredMigrations())->toContain('2025_11_18_100000_create_roles_table.php');
});

it('does not register the roles migration when the roles system is disabled', function () {
    config(['kerberos.roles_enabled' => false]);

    expect(registeredMigrations())
        ->not->toContain('2025_11_18_100000_create_roles_table.php')
        ->toContain('2025_11_18_100001_add_kerberos_columns_to_users_table.php');
});
