<?php

namespace Database\Seeders;

use App\Models\Insurer;
use Illuminate\Database\Seeder;

class InsurerSeeder extends Seeder
{
    public function run(): void
    {
        $insurers = [
            [
                'name' => 'ABC Insurance Company',
                'email' => 'info@abcinsurance.com',
                'phone' => '+1-555-0101',
                'address' => '123 Insurance Plaza, New York, NY 10001',
                'contact_person' => 'John Smith',
                'is_active' => true,
            ],
            [
                'name' => 'Global Shield Insurance',
                'email' => 'contact@globalshield.com',
                'phone' => '+1-555-0202',
                'address' => '456 Protection Blvd, Los Angeles, CA 90001',
                'contact_person' => 'Maria Garcia',
                'is_active' => true,
            ],
            [
                'name' => 'Premier Life Assurance',
                'email' => 'service@premierlife.com',
                'phone' => '+1-555-0303',
                'address' => '789 Security Street, Chicago, IL 60601',
                'contact_person' => 'David Chen',
                'is_active' => true,
            ],
            [
                'name' => 'SafeGuard Insurance Group',
                'email' => 'info@safeguard.com',
                'phone' => '+1-555-0404',
                'address' => '321 Trust Avenue, Houston, TX 77001',
                'contact_person' => 'Sarah Johnson',
                'is_active' => true,
            ],
            [
                'name' => 'United Health Coverage',
                'email' => 'contact@unitedhealthcov.com',
                'phone' => '+1-555-0505',
                'address' => '654 Wellness Way, Phoenix, AZ 85001',
                'contact_person' => 'Michael Brown',
                'is_active' => true,
            ],
            [
                'name' => 'Reliable Auto Insurance',
                'email' => 'support@reliableauto.com',
                'phone' => '+1-555-0606',
                'address' => '987 Motor Drive, Philadelphia, PA 19101',
                'contact_person' => 'Jennifer Lee',
                'is_active' => true,
            ],
            [
                'name' => 'TravelSafe Insurance Co',
                'email' => 'help@travelsafe.com',
                'phone' => '+1-555-0707',
                'address' => '147 Journey Lane, San Antonio, TX 78201',
                'contact_person' => 'Robert Martinez',
                'is_active' => true,
            ],
            [
                'name' => 'Property Shield Ltd',
                'email' => 'info@propertyshield.com',
                'phone' => '+1-555-0808',
                'address' => '258 Estate Road, San Diego, CA 92101',
                'contact_person' => 'Amanda Wilson',
                'is_active' => true,
            ],
            [
                'name' => 'Legacy Insurance (Inactive)',
                'email' => 'old@legacy.com',
                'phone' => '+1-555-0909',
                'address' => '369 Old Street, Boston, MA 02101',
                'contact_person' => 'Thomas Anderson',
                'is_active' => false,
            ],
        ];

        foreach ($insurers as $insurer) {
            Insurer::create($insurer);
        }

        $this->command->info('Insurers created successfully! (8 active, 1 inactive)');
    }
}
