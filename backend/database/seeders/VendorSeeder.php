<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoOrg = Organization::where('slug', 'demo-company')->first();
        $acmeOrg = Organization::where('slug', 'acme-corp')->first();

        // Demo organization vendors
        if ($demoOrg) {
            $this->createVendorsForOrganization($demoOrg->id, [
                [
                    'name' => 'Tech Supplies Inc.',
                    'code' => 'TECH-001',
                    'email' => 'billing@techsupplies.com',
                    'phone' => '+1 (555) 111-2222',
                    'address' => '100 Tech Park Drive',
                    'city' => 'San Jose',
                    'state' => 'CA',
                    'postal_code' => '95110',
                    'country' => 'United States',
                    'tax_id' => 'US-111222333',
                    'payment_terms' => 30,
                    'notes' => 'Primary technology vendor for hardware and software.',
                    'is_active' => true,
                ],
                [
                    'name' => 'Office Solutions LLC',
                    'code' => 'OFF-002',
                    'email' => 'accounts@officesolutions.com',
                    'phone' => '+1 (555) 333-4444',
                    'address' => '200 Commerce Blvd',
                    'city' => 'Oakland',
                    'state' => 'CA',
                    'postal_code' => '94612',
                    'country' => 'United States',
                    'tax_id' => 'US-444555666',
                    'payment_terms' => 15,
                    'notes' => 'Office furniture and supplies vendor.',
                    'is_active' => true,
                ],
                [
                    'name' => 'Cloud Services Pro',
                    'code' => 'CLD-003',
                    'email' => 'invoices@cloudservicespro.com',
                    'phone' => '+1 (555) 555-6666',
                    'address' => '300 Data Center Way',
                    'city' => 'Seattle',
                    'state' => 'WA',
                    'postal_code' => '98101',
                    'country' => 'United States',
                    'tax_id' => 'US-777888999',
                    'payment_terms' => 45,
                    'notes' => 'Cloud hosting and infrastructure services.',
                    'is_active' => true,
                ],
                [
                    'name' => 'Marketing Agency Plus',
                    'code' => 'MKT-004',
                    'email' => 'finance@marketingplus.com',
                    'phone' => '+1 (555) 777-8888',
                    'address' => '400 Creative Lane',
                    'city' => 'Los Angeles',
                    'state' => 'CA',
                    'postal_code' => '90001',
                    'country' => 'United States',
                    'tax_id' => 'US-101112131',
                    'payment_terms' => 30,
                    'notes' => 'Digital marketing and advertising services.',
                    'is_active' => true,
                ],
                [
                    'name' => 'Legacy Systems Corp',
                    'code' => 'LEG-005',
                    'email' => 'ar@legacysystems.com',
                    'phone' => '+1 (555) 999-0000',
                    'address' => '500 Old Tech Road',
                    'city' => 'Phoenix',
                    'state' => 'AZ',
                    'postal_code' => '85001',
                    'country' => 'United States',
                    'tax_id' => 'US-141516171',
                    'payment_terms' => 60,
                    'notes' => 'Legacy system maintenance - inactive vendor.',
                    'is_active' => false,
                ],
            ]);
        }

        // Acme organization vendors
        if ($acmeOrg) {
            $this->createVendorsForOrganization($acmeOrg->id, [
                [
                    'name' => 'Industrial Parts Co.',
                    'code' => 'IND-001',
                    'email' => 'sales@industrialparts.com',
                    'phone' => '+1 (555) 222-3333',
                    'address' => '600 Factory Row',
                    'city' => 'Detroit',
                    'state' => 'MI',
                    'postal_code' => '48201',
                    'country' => 'United States',
                    'tax_id' => 'US-181920212',
                    'payment_terms' => 30,
                    'notes' => 'Manufacturing parts and components.',
                    'is_active' => true,
                ],
                [
                    'name' => 'Logistics Express',
                    'code' => 'LOG-002',
                    'email' => 'billing@logisticsexpress.com',
                    'phone' => '+1 (555) 444-5555',
                    'address' => '700 Shipping Center',
                    'city' => 'Chicago',
                    'state' => 'IL',
                    'postal_code' => '60601',
                    'country' => 'United States',
                    'tax_id' => 'US-222324252',
                    'payment_terms' => 15,
                    'notes' => 'Shipping and logistics services.',
                    'is_active' => true,
                ],
            ]);
        }
    }

    /**
     * Create vendors for an organization.
     */
    private function createVendorsForOrganization(int $organizationId, array $vendors): void
    {
        foreach ($vendors as $vendorData) {
            Vendor::create(array_merge($vendorData, [
                'organization_id' => $organizationId,
            ]));
        }
    }
}
