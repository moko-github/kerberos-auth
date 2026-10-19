<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Database\Seeders;

use App\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MokoGithub\KerberosAuth\Models\Role;
use MokoGithub\KerberosAuth\Support\Kerberos;

class KerberosSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Test account is for development/staging only — never seed it in production.
        if (app()->environment('production')) {
            logger()->warning('KerberosSetupSeeder ignoré en production (compte de test non créé).');

            return;
        }

        $userModel = Kerberos::userModel();

        $withRoles = Kerberos::rolesEnabled();

        $userRole = $withRoles ? Role::where('name', 'User')->first() : null;

        if ($userRole) {
            $userModel::whereNull('role_id')->each(function ($user) use ($userRole): void {
                $user->role_id = $userRole->id;
                $user->save();
            });
        }

        $adminData = [
            'name' => 'Test User',
            'kerberos' => 'admin@krb.example.com',
            'password' => Hash::make('password'),
        ];

        if (class_exists(UserStatus::class)) {
            $adminData['status'] = UserStatus::ACTIVE;
        }

        $admin = $userModel::firstOrCreate(
            ['email' => 'admin@example.com'],
            $adminData
        );

        $adminRole = $withRoles ? Role::where('name', 'Admin')->first() : null;

        if ($adminRole) {
            $admin->role_id = $adminRole->id;
            $admin->save();
        }
    }
}
