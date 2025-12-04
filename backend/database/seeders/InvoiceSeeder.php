<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    private int $invoiceNumber = 1;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Demo Company organization
        $demoOrg = Organization::where('slug', 'demo-company')->first();
        if (!$demoOrg) {
            $this->command->warn('Demo Company organization not found.');
            return;
        }

        // Get active vendors for Demo Company
        $vendors = Vendor::where('organization_id', $demoOrg->id)
            ->where('is_active', true)
            ->get();

        if ($vendors->isEmpty()) {
            $this->command->warn('No active vendors found for Demo Company.');
            return;
        }

        // Get admin and accountant users
        $admin = User::where('organization_id', $demoOrg->id)
            ->where('email', 'admin@demo.com')
            ->first();

        $accountant = User::where('organization_id', $demoOrg->id)
            ->where('email', 'accountant@demo.com')
            ->first();

        if (!$admin) {
            $this->command->warn('Admin user not found for Demo Company.');
            return;
        }

        $this->command->info('Creating 800 invoices for Demo Company...');

        // Calculate invoices per vendor for distribution
        $totalInvoices = 800;
        $vendorCount = $vendors->count();
        $invoicesPerVendor = intdiv($totalInvoices, $vendorCount);
        $remainder = $totalInvoices % $vendorCount;

        $createdCount = 0;

        // Create invoices distributed across vendors and statuses
        foreach ($vendors as $vendorIndex => $vendor) {
            $invoicesToCreate = $invoicesPerVendor + ($vendorIndex < $remainder ? 1 : 0);

            for ($i = 0; $i < $invoicesToCreate; $i++) {
                // Distribute statuses: 40% pending, 30% approved, 20% paid, 8% overdue, 2% rejected
                $random = rand(1, 100);

                if ($random <= 40) {
                    // Pending invoices
                    $this->createPendingInvoice($vendor, $admin, $demoOrg);
                } elseif ($random <= 70) {
                    // Approved invoices
                    $this->createApprovedInvoice($vendor, $admin, $demoOrg);
                } elseif ($random <= 90) {
                    // Paid invoices
                    $this->createPaidInvoice($vendor, $admin, $demoOrg);
                } elseif ($random <= 98) {
                    // Overdue invoices
                    $this->createOverdueInvoice($vendor, $admin, $demoOrg);
                } else {
                    // Rejected invoices
                    $this->createRejectedInvoice($vendor, $admin, $demoOrg);
                }

                $createdCount++;
                if ($createdCount % 100 === 0) {
                    $this->command->line("Created {$createdCount} invoices...");
                }
            }
        }

        $this->command->info("Successfully created {$createdCount} invoices for Demo Company.");
    }

    private function createPendingInvoice(Vendor $vendor, User $admin, Organization $org): void
    {
        $subtotal = rand(50000, 500000) / 100; // 500 to 5000
        $taxAmount = round($subtotal * 0.1, 2);
        $discountAmount = 0;

        Invoice::create([
            'organization_id' => $org->id,
            'vendor_id' => $vendor->id,
            'created_by' => $admin->id,
            'invoice_number' => $this->generateInvoiceNumber($org),
            'invoice_date' => now()->subDays(rand(1, 5)),
            'due_date' => now()->addDays(rand(25, 35)),
            'status' => InvoiceStatus::Pending,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
            'currency' => 'USD',
            'description' => $this->getRandomDescription(),
            'notes' => rand(1, 10) > 7 ? $this->getRandomNotes() : null,
        ]);
    }

    private function createApprovedInvoice(Vendor $vendor, User $admin, Organization $org): void
    {
        $subtotal = rand(100000, 1000000) / 100; // 1000 to 10000
        $taxAmount = round($subtotal * 0.1, 2);
        $discountAmount = round($subtotal * 0.05, 2);

        Invoice::create([
            'organization_id' => $org->id,
            'vendor_id' => $vendor->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'invoice_number' => $this->generateInvoiceNumber($org),
            'invoice_date' => now()->subDays(rand(10, 20)),
            'due_date' => now()->addDays(rand(10, 20)),
            'status' => InvoiceStatus::Approved,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
            'currency' => 'USD',
            'description' => $this->getRandomDescription(),
            'notes' => rand(1, 10) > 7 ? $this->getRandomNotes() : null,
            'approved_at' => now()->subDays(rand(1, 5)),
        ]);
    }

    private function createPaidInvoice(Vendor $vendor, User $admin, Organization $org): void
    {
        $subtotal = rand(200000, 1500000) / 100; // 2000 to 15000
        $taxAmount = round($subtotal * 0.1, 2);
        $discountAmount = 0;

        Invoice::create([
            'organization_id' => $org->id,
            'vendor_id' => $vendor->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'invoice_number' => $this->generateInvoiceNumber($org),
            'invoice_date' => now()->subDays(rand(30, 60)),
            'due_date' => now()->subDays(rand(5, 15)),
            'status' => InvoiceStatus::Paid,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
            'currency' => 'USD',
            'description' => $this->getRandomDescription(),
            'notes' => rand(1, 10) > 8 ? $this->getRandomNotes() : null,
            'approved_at' => now()->subDays(rand(20, 40)),
            'paid_date' => now()->subDays(rand(1, 10)),
            'payment_method' => $this->getRandomPaymentMethod(),
            'payment_reference' => 'PAY-' . substr(md5(uniqid()), 0, 8),
        ]);
    }

    private function createOverdueInvoice(Vendor $vendor, User $admin, Organization $org): void
    {
        $subtotal = 3500.00;
        $taxAmount = 350.00;
        $discountAmount = 0;

        Invoice::create([
            'organization_id' => $org->id,
            'vendor_id' => $vendor->id,
            'created_by' => $admin->id,
            'invoice_number' => $this->generateInvoiceNumber($org),
            'invoice_date' => now()->subDays(45),
            'due_date' => now()->subDays(15),
            'status' => InvoiceStatus::Pending,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
            'currency' => 'USD',
            'description' => 'Overdue invoice',
            'notes' => 'This invoice requires immediate review.',
        ]);
    }

    private function createRejectedInvoice(Vendor $vendor, User $admin, Organization $org): void
    {
        $subtotal = 999.99;
        $taxAmount = 100.00;
        $discountAmount = 0;

        Invoice::create([
            'organization_id' => $org->id,
            'vendor_id' => $vendor->id,
            'created_by' => $admin->id,
            'invoice_number' => $this->generateInvoiceNumber($org),
            'invoice_date' => now()->subDays(rand(5, 10)),
            'due_date' => now()->addDays(rand(20, 30)),
            'status' => InvoiceStatus::Rejected,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
            'currency' => 'USD',
            'description' => 'Rejected - incorrect amount',
            'notes' => 'Invoice amount does not match purchase order. Please resubmit.',
        ]);
    }

    private function generateInvoiceNumber(Organization $org): string
    {
        $year = date('Y');
        return sprintf('INV-%s-%05d', $year, $this->invoiceNumber++);
    }

    private function getRandomDescription(): string
    {
        $descriptions = [
            'Professional services rendered',
            'Software development services',
            'Consulting services',
            'Technical support',
            'System maintenance',
            'Hardware procurement',
            'Office supplies',
            'Cloud services',
            'License renewal',
            'Monthly subscription',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Please process this invoice within 5 business days.',
            'Net 30 terms apply.',
            'Wire transfer preferred.',
            'Thank you for your business.',
            'Follow up required.',
            'Special pricing applied.',
            'Priority invoice - expedite processing.',
            'Standard terms apply.',
        ];

        return $notes[array_rand($notes)];
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['bank_transfer', 'credit_card', 'check', 'paypal'];
        return $methods[array_rand($methods)];
    }
}
