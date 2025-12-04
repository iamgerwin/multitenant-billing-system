<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoOrg = Organization::where('slug', 'demo-company')->first();
        $acmeOrg = Organization::where('slug', 'acme-corp')->first();

        // Demo organization users
        if ($demoOrg) {
            // Admin user
            User::create([
                'organization_id' => $demoOrg->id,
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Accountant user (read-only access)
            User::create([
                'organization_id' => $demoOrg->id,
                'name' => 'Demo Accountant',
                'email' => 'accountant@demo.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Accountant,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Acme organization users
        if ($acmeOrg) {
            // Admin user
            User::create([
                'organization_id' => $acmeOrg->id,
                'name' => 'Acme Admin',
                'email' => 'admin@acme.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Accountant user (read-only access)
            User::create([
                'organization_id' => $acmeOrg->id,
                'name' => 'Acme Accountant',
                'email' => 'accountant@acme.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Accountant,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
