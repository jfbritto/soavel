<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $appName = env('APP_NAME', 'Minha Loja de Veículos');

        $defaults = [
            // Identidade Visual
            ['key' => 'nome_sistema',          'group' => 'identidade', 'value' => $appName],
            ['key' => 'slogan',                'group' => 'identidade', 'value' => 'Seu próximo carro está aqui'],
            ['key' => 'logo_path',             'group' => 'identidade', 'value' => null],

            // Empresa
            ['key' => 'razao_social',          'group' => 'empresa',    'value' => ''],
            ['key' => 'cnpj',                  'group' => 'empresa',    'value' => ''],
            ['key' => 'cidade_estado',         'group' => 'empresa',    'value' => ''],
            ['key' => 'telefone_comercial',    'group' => 'empresa',    'value' => ''],

            // Contato e Redes Sociais
            ['key' => 'whatsapp_number',       'group' => 'contato',    'value' => ''],
            ['key' => 'instagram_url',         'group' => 'contato',    'value' => ''],
            ['key' => 'facebook_url',          'group' => 'contato',    'value' => ''],

            // Site Público
            ['key' => 'site_titulo_home',      'group' => 'site',       'value' => $appName . ' | Seminovos'],
            ['key' => 'site_descricao_home',   'group' => 'site',       'value' => 'Encontre carros seminovos de qualidade. Confira nosso estoque e encontre o veículo ideal para você.'],
        ];

        foreach ($defaults as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
