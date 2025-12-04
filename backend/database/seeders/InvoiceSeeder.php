<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoOrg = Organization::where('slug', 'demo-company')->first();

        if (!$demoOrg) {
            return;
        }

        $admin = User::where('organization_id', $demoOrg->id)
            ->where('email', 'admin@demo.com')
            ->first();

        $accountant = User::where('organization_id', $demoOrg->id)
            ->where('email', 'accountant@demo.com')
            ->first();

        $vendors = Vendor::where('organization_id', $demoOrg->id)
            ->where('is_active', true)
            ->get();

        if ($vendors->isEmpty() || !$admin) {
            return;
        }

        $invoiceNumber = 1;

        // Create pending invoices
        foreach ($vendors->take(2) as $vendor) {
            $this->createInvoice([
                'organization_id' => $demoOrg->id,
                'vendor_id' => $vendor->id,
                'invoice_number' => sprintf('INV-%s-%04d', date('Y'), $invoiceNumber++),
                'invoice_date' => Carbon::now()->subDays(rand(1, 5)),
                'due_date' => Carbon::now()->addDays(rand(25, 35)),
                'status' => InvoiceStatus::Pending,
                'subtotal' => $subtotal = rand(500, 5000) + (rand(0, 99) / 100),
                'tax_amount' => round($subtotal * 0.10, 2),
                'discount_amount' => 0,
                'currency' => 'USD',
                'description' => 'Monthly service invoice',
                'created_by' => $accountant?->id ?? $admin->id,
            ]);
        }

        // Create approved invoices
        foreach ($vendors->take(2) as $vendor) {
            $this->createInvoice([
                'organization_id' => $demoOrg->id,
                'vendor_id' => $vendor->id,
                'invoice_number' => sprintf('INV-%s-%04d', date('Y'), $invoiceNumber++),
                'invoice_date' => Carbon::now()->subDays(rand(10, 20)),
                'due_date' => Carbon::now()->addDays(rand(10, 20)),
                'status' => InvoiceStatus::Approved,
                'subtotal' => $subtotal = rand(1000, 10000) + (rand(0, 99) / 100),
                'tax_amount' => round($subtotal * 0.10, 2),
                'discount_amount' => round($subtotal * 0.05, 2),
                'currency' => 'USD',
                'description' => 'Quarterly services',
                'created_by' => $accountant?->id ?? $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(rand(1, 5)),
            ]);
        }

        // Create paid invoices
        foreach ($vendors->take(3) as $vendor) {
            $this->createInvoice([
                'organization_id' => $demoOrg->id,
                'vendor_id' => $vendor->id,
                'invoice_number' => sprintf('INV-%s-%04d', date('Y'), $invoiceNumber++),
                'invoice_date' => Carbon::now()->subDays(rand(30, 60)),
                'due_date' => Carbon::now()->subDays(rand(5, 15)),
                'status' => InvoiceStatus::Paid,
                'subtotal' => $subtotal = rand(2000, 15000) + (rand(0, 99) / 100),
                'tax_amount' => round($subtotal * 0.10, 2),
                'discount_amount' => 0,
                'currency' => 'USD',
                'description' => 'Annual subscription',
                'created_by' => $accountant?->id ?? $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(rand(20, 40)),
                'paid_date' => Carbon::now()->subDays(rand(1, 10)),
                'payment_method' => collect(['bank_transfer', 'credit_card', 'check'])->random(),
                'payment_reference' => 'PAY-' . strtoupper(substr(md5((string) rand()), 0, 8)),
            ]);
        }

        // Create rejected invoice
        $this->createInvoice([
            'organization_id' => $demoOrg->id,
            'vendor_id' => $vendors->first()->id,
            'invoice_number' => sprintf('INV-%s-%04d', date('Y'), $invoiceNumber++),
            'invoice_date' => Carbon::now()->subDays(rand(15, 25)),
            'due_date' => Carbon::now()->addDays(5),
            'status' => InvoiceStatus::Rejected,
            'subtotal' => 999.99,
            'tax_amount' => 100.00,
            'discount_amount' => 0,
            'currency' => 'USD',
            'description' => 'Rejected - incorrect amount',
            'notes' => 'Invoice amount does not match purchase order. Please resubmit.',
            'created_by' => $accountant?->id ?? $admin->id,
        ]);

        // Create overdue invoice (pending, past due date)
        $this->createInvoice([
            'organization_id' => $demoOrg->id,
            'vendor_id' => $vendors->skip(1)->first()->id,
            'invoice_number' => sprintf('INV-%s-%04d', date('Y'), $invoiceNumber++),
            'invoice_date' => Carbon::now()->subDays(45),
            'due_date' => Carbon::now()->subDays(15),
            'status' => InvoiceStatus::Pending,
            'subtotal' => 3500.00,
            'tax_amount' => 350.00,
            'discount_amount' => 0,
            'currency' => 'USD',
            'description' => 'Overdue invoice - needs attention',
            'notes' => 'This invoice requires immediate review.',
            'created_by' => $accountant?->id ?? $admin->id,
        ]);
    }

    /**
     * Create an invoice with calculated total.
     */
    private function createInvoice(array $data): Invoice
    {
        $data['total_amount'] = ($data['subtotal'] ?? 0)
            + ($data['tax_amount'] ?? 0)
            - ($data['discount_amount'] ?? 0);

        return Invoice::create($data);
    }
}
