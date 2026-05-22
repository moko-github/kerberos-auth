<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use MokoGithub\KerberosAuth\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'User'];

        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
