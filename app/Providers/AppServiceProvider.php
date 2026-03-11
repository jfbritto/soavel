<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        try {
            if (Schema::hasTable('settings')) {
                $nome = Setting::get('nome_sistema', config('adminlte.title', 'Soavel'));
                $logo = Setting::get('logo_path');

                config([
                    'adminlte.title' => $nome,
                    'adminlte.logo'  => '<b>' . e($nome) . '</b>',
                ]);

                if ($logo) {
                    config(['adminlte.logo_img' => asset('storage/' . $logo)]);
                }
            }
        } catch (\Throwable $e) {
            // silencioso — banco pode não existir ainda durante migrations
        }
    }
}
