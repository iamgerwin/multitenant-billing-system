<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Str;

class InvoiceNumberGenerator
{
    /**
     * Generate a unique invoice number.
     *
     * Format: [ORG-3-LETTERS]-[Ymd]-[NanoID]
     * Example: DEM-20251204-a1b2c3d4
     */
    public function generate(Organization $organization): string
    {
        $prefix = $this->getOrganizationPrefix($organization);
        $date = date('Ymd');
        $nanoId = $this->generateNanoId();

        return sprintf('%s-%s-%s', $prefix, $date, $nanoId);
    }

    /**
     * Get the first 3 letters of the organization name (uppercase).
     */
    private function getOrganizationPrefix(Organization $organization): string
    {
        // Remove non-alphanumeric characters and get first 3 letters
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $organization->name);

        return strtoupper(substr($cleanName, 0, 3));
    }

    /**
     * Generate a NanoID-like unique identifier.
     *
     * Uses URL-safe characters: A-Za-z0-9
     * Default length: 8 characters
     */
    private function generateNanoId(int $length = 8): string
    {
        return strtolower(Str::random($length));
    }
}
