<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FipeController extends Controller
{
    private const BASES = [
        'https://parallelum.com.br/fipe/api/v1/carros',
        'https://fipe.parallelum.com.br/api/v1/carros',
    ];

    private function fipeGet(string $path)
    {
        foreach (self::BASES as $base) {
            try {
                $res = Http::timeout(8)->get($base . $path);
                if ($res->successful()) {
                    return $res->json();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return [];
    }

    public function marcas()
    {
        $data = Cache::remember('fipe_marcas', 86400, fn () => $this->fipeGet('/marcas'));
        return response()->json($data);
    }

    public function modelos(string $marca)
    {
        $data = Cache::remember("fipe_modelos_{$marca}", 86400, fn () => $this->fipeGet("/marcas/{$marca}/modelos"));
        return response()->json($data);
    }

    public function anos(string $marca, string $modelo)
    {
        $data = Cache::remember("fipe_anos_{$marca}_{$modelo}", 86400, fn () => $this->fipeGet("/marcas/{$marca}/modelos/{$modelo}/anos"));
        return response()->json($data);
    }

    public function preco(string $marca, string $modelo, string $ano)
    {
        $data = Cache::remember("fipe_preco_{$marca}_{$modelo}_{$ano}", 3600, fn () => $this->fipeGet("/marcas/{$marca}/modelos/{$modelo}/anos/{$ano}"));
        return response()->json($data);
    }
}
