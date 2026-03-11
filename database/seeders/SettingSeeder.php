<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            // Identidade Visual
            ['key' => 'nome_sistema',          'group' => 'identidade', 'value' => 'Soavel Veículos'],
            ['key' => 'slogan',                'group' => 'identidade', 'value' => 'Seu próximo carro está aqui'],
            ['key' => 'logo_path',             'group' => 'identidade', 'value' => null],

            // Empresa
            ['key' => 'razao_social',          'group' => 'empresa',    'value' => ''],
            ['key' => 'cnpj',                  'group' => 'empresa',    'value' => ''],
            ['key' => 'cidade_estado',         'group' => 'empresa',    'value' => 'Santa Maria de Jetibá – ES'],
            ['key' => 'telefone_comercial',    'group' => 'empresa',    'value' => ''],

            // Contato e Redes Sociais
            ['key' => 'whatsapp_number',       'group' => 'contato',    'value' => '5527998490472'],
            ['key' => 'instagram_url',         'group' => 'contato',    'value' => ''],
            ['key' => 'facebook_url',          'group' => 'contato',    'value' => ''],

            // Site Público
            ['key' => 'site_titulo_home',      'group' => 'site',       'value' => 'Soavel Veículos | Seminovos'],
            ['key' => 'site_descricao_home',   'group' => 'site',       'value' => 'Carros Seminovos em Santa Maria de Jetibá, ES'],

            // Rodapé
            ['key' => 'footer_texto',          'group' => 'rodape',     'value' => 'Desenvolvido por HELPFLUX SOLUÇÕES EM TECNOLOGIA LTDA'],
        ];

        foreach ($defaults as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
