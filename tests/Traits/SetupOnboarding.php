<?php

namespace Tests\Traits;

use App\Models\Setting;

trait SetupOnboarding
{
    protected function completeOnboarding(): void
    {
        Setting::set('nome_sistema', 'Soavel Teste');
        Setting::set('whatsapp_number', '28999990000');
    }
}
