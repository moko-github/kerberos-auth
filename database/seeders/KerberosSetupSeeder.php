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

        $userRole = Role::where('name', 'User')->first();

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

        $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole) {
            $admin->role_id = $adminRole->id;
            $admin->save();
        }
    }
}
