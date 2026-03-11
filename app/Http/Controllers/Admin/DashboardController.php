<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $mes  = now()->month;
        $ano  = now()->year;

        $faturamentoMes = Sale::whereMonth('data_venda', $mes)
            ->whereYear('data_venda', $ano)
            ->where('status', 'concluida')
            ->sum('preco_venda');

        $custoVeiculosMes = Sale::whereMonth('data_venda', $mes)
            ->whereYear('data_venda', $ano)
            ->where('sales.status', 'concluida')
            ->join('vehicles', 'sales.vehicle_id', '=', 'vehicles.id')
            ->sum('vehicles.preco_compra');

        $despesasMes = Expense::whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->sum('valor');

        $stats = [
            'total_disponiveis'   => Vehicle::where('status', 'disponivel')->count(),
            'total_reservados'    => Vehicle::where('status', 'reservado')->count(),
            'total_vendidos_mes'  => Sale::whereMonth('data_venda', $mes)->whereYear('data_venda', $ano)->where('status', 'concluida')->count(),
            'faturamento_mes'     => $faturamentoMes,
            'despesas_mes'        => $despesasMes,
            'lucro_mes'           => $faturamentoMes - $custoVeiculosMes - $despesasMes,
            'leads_novos'         => Lead::where('status', 'novo')->count(),
            'leads_em_contato'    => Lead::where('status', 'em_contato')->count(),
        ];

        $vendasRecentes = Sale::with(['vehicle', 'customer'])
            ->latest('data_venda')
            ->take(5)
            ->get();

        $leadsRecentes = Lead::with('vehicle')
            ->where('status', 'novo')
            ->latest()
            ->take(5)
            ->get();

        // Vendas por mês (últimos 6 meses)
        $vendasPorMes = Sale::select(
                DB::raw('MONTH(data_venda) as mes'),
                DB::raw('YEAR(data_venda) as year'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(preco_venda) as faturamento')
            )
            ->where('status', 'concluida')
            ->where('data_venda', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('year', 'mes')
            ->orderBy('year')
            ->orderBy('mes')
            ->get();

        // Veículos por categoria
        $veiculosPorCategoria = Vehicle::select('categoria', DB::raw('count(*) as total'))
            ->where('status', 'disponivel')
            ->groupBy('categoria')
            ->get();

        // ── Estoque: valores totais e divisão loja vs sócios ──────────────────
        $veiculosEmEstoque = Vehicle::whereIn('status', ['disponivel', 'reservado'])
            ->with('partners')
            ->get(['id', 'preco', 'preco_compra']);

        $estoque = [
            'total_venda'   => 0,
            'total_custo'   => 0,
            'loja_venda'    => 0,
            'loja_custo'    => 0,
            'socios_venda'  => 0,
            'socios_custo'  => 0,
            'qtd'           => $veiculosEmEstoque->count(),
        ];

        foreach ($veiculosEmEstoque as $v) {
            $percentualSocios = $v->partners->sum('pivot.percentual');   // 0–100
            $percentualLoja   = 100 - $percentualSocios;

            $preco  = (float) ($v->preco        ?? 0);
            $custo  = (float) ($v->preco_compra ?? 0);

            $estoque['total_venda']  += $preco;
            $estoque['total_custo']  += $custo;
            $estoque['loja_venda']   += $preco * $percentualLoja   / 100;
            $estoque['loja_custo']   += $custo * $percentualLoja   / 100;
            $estoque['socios_venda'] += $preco * $percentualSocios / 100;
            $estoque['socios_custo'] += $custo * $percentualSocios / 100;
        }

        return view('admin.dashboard.index', compact(
            'stats', 'vendasRecentes', 'leadsRecentes', 'vendasPorMes', 'veiculosPorCategoria', 'estoque'
        ));
    }
}
