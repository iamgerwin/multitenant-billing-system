<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo organization
        Organization::create([
            'name' => 'Demo Company',
            'slug' => 'demo-company',
            'email' => 'info@demo-company.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Business Street',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94102',
            'country' => 'United States',
            'tax_id' => 'US-123456789',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        // Create secondary organization for testing multi-tenancy
        Organization::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
            'email' => 'contact@acme-corp.com',
            'phone' => '+1 (555) 987-6543',
            'address' => '456 Enterprise Avenue',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'United States',
            'tax_id' => 'US-987654321',
            'currency' => 'USD',
            'is_active' => true,
        ]);
    }
}
