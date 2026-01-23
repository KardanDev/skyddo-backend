<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            // Individual Clients
            [
                'name' => 'James Anderson',
                'email' => 'james.anderson@email.com',
                'phone' => '+1-555-1001',
                'address' => '123 Maple Street, Springfield, IL 62701',
                'id_number' => 'DL-123456789',
                'company_name' => null,
            ],
            [
                'name' => 'Emily Thompson',
                'email' => 'emily.thompson@email.com',
                'phone' => '+1-555-1002',
                'address' => '456 Oak Avenue, Portland, OR 97201',
                'id_number' => 'DL-987654321',
                'company_name' => null,
            ],
            [
                'name' => 'Michael Rodriguez',
                'email' => 'michael.rodriguez@email.com',
                'phone' => '+1-555-1003',
                'address' => '789 Pine Road, Denver, CO 80201',
                'id_number' => 'DL-456789123',
                'company_name' => null,
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@email.com',
                'phone' => '+1-555-1004',
                'address' => '321 Elm Street, Seattle, WA 98101',
                'id_number' => 'DL-789123456',
                'company_name' => null,
            ],
            [
                'name' => 'David Martinez',
                'email' => 'david.martinez@email.com',
                'phone' => '+1-555-1005',
                'address' => '654 Birch Lane, Austin, TX 78701',
                'id_number' => 'DL-321654987',
                'company_name' => null,
            ],
            [
                'name' => 'Jennifer Lee',
                'email' => 'jennifer.lee@email.com',
                'phone' => '+1-555-1006',
                'address' => '987 Cedar Drive, Miami, FL 33101',
                'id_number' => 'DL-654987321',
                'company_name' => null,
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@email.com',
                'phone' => '+1-555-1007',
                'address' => '147 Walnut Court, Boston, MA 02101',
                'id_number' => 'DL-147258369',
                'company_name' => null,
            ],
            [
                'name' => 'Jessica Brown',
                'email' => 'jessica.brown@email.com',
                'phone' => '+1-555-1008',
                'address' => '258 Spruce Way, Atlanta, GA 30301',
                'id_number' => 'DL-369258147',
                'company_name' => null,
            ],
            [
                'name' => 'Christopher Davis',
                'email' => 'christopher.davis@email.com',
                'phone' => '+1-555-1009',
                'address' => '369 Willow Street, Phoenix, AZ 85001',
                'id_number' => 'DL-258147369',
                'company_name' => null,
            ],
            [
                'name' => 'Amanda Wilson',
                'email' => 'amanda.wilson@email.com',
                'phone' => '+1-555-1010',
                'address' => '741 Ash Boulevard, San Francisco, CA 94101',
                'id_number' => 'DL-741852963',
                'company_name' => null,
            ],
            // Corporate Clients
            [
                'name' => 'Tech Innovations Inc',
                'email' => 'info@techinnovations.com',
                'phone' => '+1-555-2001',
                'address' => '100 Technology Drive, Silicon Valley, CA 94025',
                'id_number' => 'TAX-100200300',
                'company_name' => 'Tech Innovations Inc',
            ],
            [
                'name' => 'Green Energy Solutions',
                'email' => 'contact@greenenergy.com',
                'phone' => '+1-555-2002',
                'address' => '200 Renewable Way, Boulder, CO 80301',
                'id_number' => 'TAX-200300400',
                'company_name' => 'Green Energy Solutions LLC',
            ],
            [
                'name' => 'Healthcare Plus Group',
                'email' => 'admin@healthcareplus.com',
                'phone' => '+1-555-2003',
                'address' => '300 Medical Center Blvd, Chicago, IL 60601',
                'id_number' => 'TAX-300400500',
                'company_name' => 'Healthcare Plus Group Corp',
            ],
            [
                'name' => 'Construction Masters LLC',
                'email' => 'office@constructionmasters.com',
                'phone' => '+1-555-2004',
                'address' => '400 Builder Street, Dallas, TX 75201',
                'id_number' => 'TAX-400500600',
                'company_name' => 'Construction Masters LLC',
            ],
            [
                'name' => 'Retail World Corporation',
                'email' => 'corporate@retailworld.com',
                'phone' => '+1-555-2005',
                'address' => '500 Shopping Plaza, New York, NY 10001',
                'id_number' => 'TAX-500600700',
                'company_name' => 'Retail World Corporation',
            ],
        ];

        $members = User::where('role', User::ROLE_MEMBER)->get();

        foreach ($clients as $index => $clientData) {
            $client = Client::create($clientData);

            // Assign clients to members (round-robin)
            if ($members->isNotEmpty()) {
                $member = $members[$index % $members->count()];
                $client->users()->attach($member->id, [
                    'role' => $index < 5 ? 'owner' : ($index < 10 ? 'manager' : 'viewer'),
                ]);
            }
        }

        $this->command->info('Clients created successfully! (10 individual, 5 corporate)');
        $this->command->info('Clients assigned to team members with various roles');
    }
}
