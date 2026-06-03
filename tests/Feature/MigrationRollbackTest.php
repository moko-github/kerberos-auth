<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

function migration(string $file): object
{
    return require dirname(__DIR__, 2).'/database/migrations/'.$file;
}

it('rolls back the kerberos column even when the package unique index is absent', function () {
    // Simulate a host table whose kerberos column pre-existed without the
    // package's unique index (the scenario the idempotent up() guards against).
    Schema::table('users', fn (Blueprint $table) => $table->dropUnique(['kerberos']));

    migration('2025_11_18_100001_add_kerberos_columns_to_users_table.php')->down();

    expect(Schema::hasColumn('users', 'kerberos'))->toBeFalse();
});

it('rolls back role_id even when the package foreign key is absent', function () {
    Schema::table('users', fn (Blueprint $table) => $table->dropForeign(['role_id']));

    migration('2025_11_18_100000_create_roles_table.php')->down();

    expect(Schema::hasColumn('users', 'role_id'))->toBeFalse();
});
