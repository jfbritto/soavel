<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckSuspended
{
    public function handle(Request $request, Closure $next)
    {
        // Não bloqueia rotas de API do master
        if ($request->is('api/master/*')) {
            return $next($request);
        }

        $suspended = DB::table('settings')
            ->where('key', 'suspended')
            ->value('value');

        if ($suspended === 'true') {
            // Se for admin logado, mostra banner mas permite acesso
            if (auth()->check()) {
                session()->flash('suspended_warning', 'Este site está suspenso. Entre em contato com o suporte para regularizar.');
                return $next($request);
            }

            // Site público: mostra página de manutenção
            return response()->view('errors.suspended', [], 503);
        }

        return $next($request);
    }
}
