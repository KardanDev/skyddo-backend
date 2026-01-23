<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanySetting::create([
            'company_name' => 'Skyddo Corretora de Seguros, Lda.',
            'company_email' => 'geral@skyddo.co.mz',
            'company_phone' => '+258 21 321 097',
            'company_address' => 'Avenida Julius Nyerere, Nº 3344, Maputo, Moçambique',
            'website' => 'https://www.skyddo.co.mz',
            'tax_id' => '400123456',
            'description' => 'A Skyddo Corretora de Seguros é uma empresa 100% moçambicana dedicada à prestação de serviços de consultoria e intermediação de seguros. Com uma equipa altamente qualificada e experiente, oferecemos soluções personalizadas em seguros de vida, saúde, automóveis, propriedade, responsabilidade civil e seguros empresariais. A nossa missão é proteger o que mais importa para os nossos clientes através de produtos de seguros competitivos e um atendimento de excelência.',
        ]);

        $this->command->info('✅ Company settings created successfully!');
        $this->command->info('');
        $this->command->info('🏢 Company Information:');
        $this->command->info('┌────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ Name: Skyddo Corretora de Seguros, Lda.                       │');
        $this->command->info('│ Email: geral@skyddo.co.mz                                      │');
        $this->command->info('│ Phone: +258 21 321 097                                         │');
        $this->command->info('│ NUIT: 400123456                                                │');
        $this->command->info('│ Website: https://www.skyddo.co.mz                              │');
        $this->command->info('└────────────────────────────────────────────────────────────────┘');
    }
}
