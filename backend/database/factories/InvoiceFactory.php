<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $taxAmount = round($subtotal * 0.1, 2);
        $discountAmount = round(fake()->randomFloat(2, 0, $subtotal * 0.1), 2);

        return [
            'organization_id' => Organization::factory(),
            'vendor_id' => Vendor::factory(),
            'created_by' => User::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->numerify('######'),
            'invoice_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+60 days'),
            'status' => InvoiceStatus::Pending,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
            'currency' => 'USD',
            'description' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the invoice is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Pending,
        ]);
    }

    /**
     * Indicate that the invoice is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Rejected,
        ]);
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Paid,
            'approved_at' => now()->subDays(5),
            'paid_date' => now(),
            'payment_method' => fake()->randomElement(['Bank Transfer', 'Credit Card', 'Check', 'PayPal']),
            'payment_reference' => fake()->uuid(),
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Pending,
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Configure the invoice to belong to a specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Configure the invoice to belong to a specific vendor.
     */
    public function forVendor(Vendor $vendor): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_id' => $vendor->id,
            'organization_id' => $vendor->organization_id,
        ]);
    }

    /**
     * Configure the invoice to be created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
    }
}
