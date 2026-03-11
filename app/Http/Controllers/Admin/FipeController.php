<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FipeController extends Controller
{
    private const BASE = 'https://parallelum.com.br/fipe/api/v1/carros';

    public function marcas()
    {
        $data = Cache::remember('fipe_marcas', 86400, function () {
            $res = Http::timeout(10)->get(self::BASE . '/marcas');
            return $res->successful() ? $res->json() : [];
        });

        return response()->json($data);
    }

    public function modelos(string $marca)
    {
        $data = Cache::remember("fipe_modelos_{$marca}", 86400, function () use ($marca) {
            $res = Http::timeout(10)->get(self::BASE . "/marcas/{$marca}/modelos");
            return $res->successful() ? $res->json() : [];
        });

        return response()->json($data);
    }

    public function anos(string $marca, string $modelo)
    {
        $data = Cache::remember("fipe_anos_{$marca}_{$modelo}", 86400, function () use ($marca, $modelo) {
            $res = Http::timeout(10)->get(self::BASE . "/marcas/{$marca}/modelos/{$modelo}/anos");
            return $res->successful() ? $res->json() : [];
        });

        return response()->json($data);
    }

    public function preco(string $marca, string $modelo, string $ano)
    {
        $data = Cache::remember("fipe_preco_{$marca}_{$modelo}_{$ano}", 3600, function () use ($marca, $modelo, $ano) {
            $res = Http::timeout(10)->get(self::BASE . "/marcas/{$marca}/modelos/{$modelo}/anos/{$ano}");
            return $res->successful() ? $res->json() : [];
        });

        return response()->json($data);
    }
}
