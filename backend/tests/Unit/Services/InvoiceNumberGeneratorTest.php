<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new InvoiceNumberGenerator();
    }

    public function test_generate_returns_correctly_formatted_invoice_number(): void
    {
        $organization = Organization::factory()->create(['name' => 'Demo Company']);

        $result = $this->generator->generate($organization);

        // Format: PREFIX-YYYYMMDD-NANOID (e.g., DEM-20241204-a1b2c3d4)
        $this->assertMatchesRegularExpression('/^[A-Z]{2,3}-\d{8}-[a-z0-9]{8}$/', $result);
    }

    public function test_generate_uses_first_three_letters_of_org_name(): void
    {
        $organization = Organization::factory()->create(['name' => 'Acme Corporation']);

        $result = $this->generator->generate($organization);

        $this->assertStringStartsWith('ACM-', $result);
    }

    public function test_generate_handles_short_org_names(): void
    {
        $organization = Organization::factory()->create(['name' => 'AB']);

        $result = $this->generator->generate($organization);

        $this->assertStringStartsWith('AB-', $result);
    }

    public function test_generate_strips_non_alphanumeric_characters(): void
    {
        $organization = Organization::factory()->create(['name' => 'Test & Co. Inc.']);

        $result = $this->generator->generate($organization);

        $this->assertStringStartsWith('TES-', $result);
    }

    public function test_generate_includes_current_date(): void
    {
        $organization = Organization::factory()->create(['name' => 'Test Company']);
        $expectedDate = date('Ymd');

        $result = $this->generator->generate($organization);

        $this->assertStringContainsString("-{$expectedDate}-", $result);
    }

    public function test_generate_produces_unique_numbers(): void
    {
        $organization = Organization::factory()->create(['name' => 'Test Company']);

        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $results[] = $this->generator->generate($organization);
        }

        $this->assertCount(100, array_unique($results));
    }

    public function test_generate_handles_org_name_with_numbers(): void
    {
        $organization = Organization::factory()->create(['name' => '123 Tech Labs']);

        $result = $this->generator->generate($organization);

        // Should start with first 3 alphanumeric chars (including numbers)
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{2,3}-\d{8}-[a-z0-9]{8}$/', $result);
    }

    public function test_generate_handles_lowercase_org_name(): void
    {
        $organization = Organization::factory()->create(['name' => 'acme corp']);

        $result = $this->generator->generate($organization);

        // Should convert to uppercase
        $this->assertStringStartsWith('ACM-', $result);
    }
}
