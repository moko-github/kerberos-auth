<?php

namespace Database\Seeders;

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

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test User',
                'kerberos' => 'admin@krb.example.com',
                'password' => Hash::make('password'),
            ]
        );

        $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole) {
            $admin->role_id = $adminRole->id;
            $admin->save();
        }
    }
}
