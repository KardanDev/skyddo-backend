<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super User - System Owner
        User::create([
            'name' => 'SuperUser',
            'email' => 'admin@skyddo.co.mz',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SUPER_USER,
            'phone' => '+258 82 000 0001',
            'position' => 'Developer',
            'bio' => 'Developer Access',
        ]);

        // Admin - Operations Director
        User::create([
            'name' => 'Graça Machel',
            'email' => 'graca.machel@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'phone' => '+258 84 111 2222',
            'position' => 'Directora de Operações',
            'bio' => 'Directora de Operações com vasta experiência em gestão de equipas e processos operacionais. Especialista em seguros corporativos e gestão de carteiras de clientes empresariais. Responsável pela coordenação de todas as operações diárias da corretora.',
        ]);

        // Admin - Commercial Director
        User::create([
            'name' => 'Samora Moisés',
            'email' => 'samora.moises@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'phone' => '+258 87 333 4444',
            'position' => 'Director Comercial',
            'bio' => 'Director Comercial responsável pela estratégia de vendas e expansão de mercado. Licenciado em Marketing com especialização em Vendas Consultivas. Experiência comprovada no desenvolvimento de parcerias estratégicas com seguradoras e na gestão de equipas comerciais de alto desempenho.',
        ]);

        // Member - Senior Life Insurance Consultant
        User::create([
            'name' => 'Lurdes Mutola',
            'email' => 'lurdes.mutola@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'phone' => '+258 84 555 6666',
            'position' => 'Consultora Sénior - Seguros de Vida',
            'bio' => 'Consultora especializada em seguros de vida, saúde e planos de reforma. Certificada pela ISSM (Instituto de Supervisão de Seguros de Moçambique). Mais de 10 anos de experiência em consultoria personalizada, ajudando famílias e profissionais a protegerem o seu futuro financeiro.',
        ]);

        // Member - Auto & Property Insurance Specialist
        User::create([
            'name' => 'Eusébio da Silva',
            'email' => 'eusebio.silva@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'phone' => '+258 86 777 8888',
            'position' => 'Especialista em Seguros Auto e Propriedade',
            'bio' => 'Especialista em seguros de automóveis, frotas empresariais e seguros de propriedade. Certificação avançada em avaliação de riscos e sinistros. Reconhecido pela excelência no atendimento ao cliente e pela capacidade de encontrar soluções adequadas às necessidades específicas de cada cliente.',
        ]);

        // Member - Corporate Insurance Consultant
        User::create([
            'name' => 'Maria de Lurdes Arone',
            'email' => 'maria.arone@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'phone' => '+258 82 999 0000',
            'position' => 'Consultora de Seguros Empresariais',
            'bio' => 'Consultora focada em seguros para PMEs e grandes empresas. Especialização em seguros de responsabilidade civil, acidentes de trabalho e seguros multirriscos empresariais. Licenciada em Contabilidade e Auditoria, com profundo conhecimento das necessidades de protecção das empresas moçambicanas.',
        ]);

        // Member - Claims Manager
        User::create([
            'name' => 'Alberto Mangue',
            'email' => 'alberto.mangue@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'phone' => '+258 84 222 3333',
            'position' => 'Gestor de Sinistros',
            'bio' => 'Gestor de Sinistros responsável pelo acompanhamento de processos de reclamação junto às seguradoras. Especialista em negociação e resolução de sinistros complexos. Comprometido em garantir que os clientes recebam as indemnizações devidas de forma rápida e justa.',
        ]);

        // Member - Customer Success Agent
        User::create([
            'name' => 'Sheila Nhantumbo',
            'email' => 'sheila.nhantumbo@skyddo.co.mz',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'phone' => '+258 87 444 5555',
            'position' => 'Agente de Atendimento ao Cliente',
            'bio' => 'Agente dedicada ao atendimento e satisfação do cliente. Responsável pelo suporte pós-venda, renovações de apólices e esclarecimento de dúvidas. Formação em Relações Públicas e experiência em gestão de relacionamento com clientes no sector de serviços financeiros.',
        ]);

        $this->command->info('✅ Users created successfully with complete profile information!');
        $this->command->info('');
        $this->command->info('👥 Total Users: 8 (1 Super User, 2 Admins, 5 Members)');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('┌────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ SUPER USER (Full System Access)                                    │');
        $this->command->info('├────────────────────────────────────────────────────────────────────┤');
        $this->command->info('│ admin@skyddo.co.mz / password123 Developer Access                  │');
        $this->command->info('├────────────────────────────────────────────────────────────────────┤');
        $this->command->info('│ ADMINS (Can Edit Company Settings & Manage All)                   │');
        $this->command->info('├────────────────────────────────────────────────────────────────────┤');
        $this->command->info('│ graca.machel@skyddo.co.mz / password (Directora de Operações)     │');
        $this->command->info('│ samora.moises@skyddo.co.mz / password (Director Comercial)        │');
        $this->command->info('├────────────────────────────────────────────────────────────────────┤');
        $this->command->info('│ MEMBERS (Can Only Edit Their Own Profile)                         │');
        $this->command->info('├────────────────────────────────────────────────────────────────────┤');
        $this->command->info('│ lurdes.mutola@skyddo.co.mz / password (Consultora Sénior)         │');
        $this->command->info('│ eusebio.silva@skyddo.co.mz / password (Especialista Auto)         │');
        $this->command->info('│ maria.arone@skyddo.co.mz / password (Consultora Empresarial)      │');
        $this->command->info('│ alberto.mangue@skyddo.co.mz / password (Gestor de Sinistros)      │');
        $this->command->info('│ sheila.nhantumbo@skyddo.co.mz / password (Atendimento)            │');
        $this->command->info('└────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');
        $this->command->info('💡 All passwords are set to: password');
    }
}
