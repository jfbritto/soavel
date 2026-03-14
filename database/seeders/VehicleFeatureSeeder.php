<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VehicleFeatureSeeder extends Seeder
{
    /**
     * Lista canônica de opcionais disponíveis para seleção no admin.
     * Esses são os opcionais padrão do mercado brasileiro.
     */
    public static array $featuresByCategory = [
        'Conforto / Interior' => [
            'Ar-condicionado',
            'Ar-condicionado digital',
            'Direção hidráulica',
            'Direção elétrica',
            'Vidros elétricos',
            'Travas elétricas',
            'Retrovisores elétricos',
            'Banco do motorista com regulagem elétrica',
            'Banco do carona com regulagem elétrica',
            'Volante com regulagem de altura',
            'Volante multifuncional',
            'Teto solar',
            'Teto panorâmico',
            'Bancos de couro',
            'Bancos aquecidos',
            'Banco bipartido rebatível',
            'Para-brisa com desembaçador',
        ],
        'Segurança' => [
            'Airbag motorista',
            'Airbag duplo',
            'Airbag lateral',
            'Airbag cortina',
            'ABS',
            'ESP (Controle de estabilidade)',
            'Controle de tração',
            'Assistente de partida em rampa',
            'Câmera de ré',
            'Sensor de ré',
            'Sensor de estacionamento dianteiro',
            'Alerta de ponto cego',
            'Frenagem automática de emergência',
        ],
        'Tecnologia / Entretenimento' => [
            'Central multimídia',
            'Apple CarPlay',
            'Android Auto',
            'Bluetooth',
            'GPS / Navegador',
            'Comandos de voz',
            'Carregador wireless',
            'Entrada USB',
            'Tomada 12V',
        ],
        'Motor / Mecânica' => [
            'Start/Stop automático',
            'Piloto automático / Cruise control',
            'Câmbio automático',
            'Câmbio CVT',
            'Seletor de modo de condução',
            'Tração 4x4',
            'Suspensão rebaixada',
        ],
        'Estética / Exterior' => [
            'Rodas de liga leve',
            'Faróis de LED',
            'Faróis de Xenon',
            'Luz de rodagem diurna (DRL)',
        ],
    ];

    /** Lista plana para retrocompatibilidade */
    public static array $features = [];

    public static function boot(): void
    {
        // Popula a lista plana a partir das categorias
    }

    public static function allFeatures(): array
    {
        $all = [];
        foreach (static::$featuresByCategory as $items) {
            $all = array_merge($all, $items);
        }
        return $all;
    }

    public function run()
    {
        // Este seeder não insere no banco — a lista é usada dinamicamente pelo admin
        // para exibir checkboxes. Os opcionais são salvos por veículo.
        $this->command->info('Lista de opcionais disponível em VehicleFeatureSeeder::$features');
    }
}
