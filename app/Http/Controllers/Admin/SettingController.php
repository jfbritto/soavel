<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    private const FIELDS = [
        'identidade' => ['nome_sistema', 'slogan'],
        'empresa'    => ['razao_social', 'cnpj', 'cidade_estado', 'telefone_comercial', 'endereco_completo', 'horario_atendimento', 'descricao_empresa', 'maps_embed_url'],
        'contato'    => ['whatsapp_number', 'instagram_url', 'facebook_url'],
        'site'       => ['site_titulo_home', 'site_descricao_home', 'site_layout', 'hero_titulo', 'stat_clientes', 'stat_anos', 'cor_primaria'],
    ];

    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo_path'    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'favicon_path' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:512',
        ]);

        // Salvar campos de texto
        foreach (self::FIELDS as $group => $keys) {
            foreach ($keys as $key) {
                Setting::set($key, $request->input($key, ''), $group);
            }
        }

        // Salvar logo se enviado; ou remover se solicitado
        if ($request->hasFile('logo_path')) {
            $old = Setting::get('logo_path');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('logo_path')->store('settings', 'public');
            Setting::set('logo_path', $path, 'identidade');
        } elseif ($request->input('remove_logo') === '1') {
            $old = Setting::get('logo_path');
            if ($old) Storage::disk('public')->delete($old);
            Setting::set('logo_path', null, 'identidade');
        }

        // Salvar favicon se enviado; ou remover se solicitado
        if ($request->hasFile('favicon_path')) {
            $old = Setting::get('favicon_path');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('favicon_path')->store('settings', 'public');
            Setting::set('favicon_path', $path, 'identidade');
        } elseif ($request->input('remove_favicon') === '1') {
            $old = Setting::get('favicon_path');
            if ($old) Storage::disk('public')->delete($old);
            Setting::set('favicon_path', null, 'identidade');
        }

        Setting::flushAll();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
