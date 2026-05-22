<?php

namespace MokoGithub\KerberosAuth\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MokoGithub\KerberosAuth\Models\Role;

class KerberosSetupSeeder extends Seeder
{
    public function run(): void
    {
        $userRole = Role::where('name', 'User')->first();

        if ($userRole) {
            User::whereNull('role_id')->each(function (User $user) use ($userRole): void {
                $user->role_id = $userRole->id;
                $user->save();
            });
        }

        $adminData = [
            'name' => 'Test User',
            'kerberos' => 'admin@krb.example.com',
            'password' => Hash::make('password'),
        ];

        if (class_exists(\App\Enums\UserStatus::class)) {
            $adminData['status'] = \App\Enums\UserStatus::ACTIVE;
        }

        $admin = User::firstOrCreate(
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
