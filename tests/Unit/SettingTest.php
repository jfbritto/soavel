<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_and_get()
    {
        Setting::set('nome_sistema', 'Soavel Veículos');
        $this->assertEquals('Soavel Veículos', Setting::get('nome_sistema'));
    }

    public function test_get_returns_default_when_not_found()
    {
        $this->assertEquals('padrão', Setting::get('inexistente', 'padrão'));
    }

    public function test_set_updates_existing()
    {
        Setting::set('cor', 'azul');
        Setting::set('cor', 'vermelho');

        $this->assertEquals('vermelho', Setting::get('cor'));
        $this->assertEquals(1, Setting::where('key', 'cor')->count());
    }

    public function test_set_uses_group()
    {
        Setting::set('whatsapp', '28999990000', 'contato');

        $setting = Setting::where('key', 'whatsapp')->first();
        $this->assertEquals('contato', $setting->group);
    }
}
