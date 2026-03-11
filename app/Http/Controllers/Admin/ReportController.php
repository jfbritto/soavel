<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function financial(Request $request)
    {
        $ano = $request->get('ano', now()->year);
        $mes = $request->get('mes');

        $salesQuery    = Sale::where('status', 'concluida')->whereYear('data_venda', $ano);
        $expenseQuery  = Expense::whereYear('data', $ano);

        if ($mes) {
            $salesQuery->whereMonth('data_venda', $mes);
            $expenseQuery->whereMonth('data', $mes);
        }

        $vendas   = $salesQuery->with(['vehicle', 'customer'])->latest('data_venda')->get();
        $despesas = $expenseQuery->with(['vehicle'])->latest('data')->get();

        $faturamento = $vendas->sum('preco_venda');
        $custoVeiculos = $vendas->sum(fn($s) => $s->vehicle?->preco_compra ?? 0);
        $totalDespesas = $despesas->sum('valor');
        $lucro = $faturamento - $custoVeiculos - $totalDespesas;

        // Resumo por mês
        $resumoPorMes = Sale::select(
                DB::raw('MONTH(data_venda) as mes'),
                DB::raw('COUNT(*) as qtd_vendas'),
                DB::raw('SUM(preco_venda) as faturamento')
            )
            ->where('status', 'concluida')
            ->whereYear('data_venda', $ano)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $anos = Sale::select(DB::raw('YEAR(data_venda) as ano'))
            ->groupBy('ano')
            ->orderByDesc('ano')
            ->pluck('ano');

        return view('admin.reports.financial', compact(
            'vendas', 'despesas', 'faturamento', 'custoVeiculos',
            'totalDespesas', 'lucro', 'resumoPorMes', 'anos', 'ano', 'mes'
        ));
    }

    public function vehicles(Request $request)
    {
        $status    = $request->get('status', 'disponivel');
        $categoria = $request->get('categoria');

        $query = Vehicle::with('principalPhoto')->withCount(['photos', 'expenses']);

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        $vehicles = $query->orderBy('marca')->get();

        $resumo = [
            'disponivel' => Vehicle::where('status', 'disponivel')->count(),
            'reservado'  => Vehicle::where('status', 'reservado')->count(),
            'vendido'    => Vehicle::where('status', 'vendido')->count(),
            'total'      => Vehicle::count(),
        ];

        return view('admin.reports.vehicles', compact('vehicles', 'resumo', 'status', 'categoria'));
    }

    public function exportFinancial(Request $request)
    {
        $ano  = $request->get('ano', now()->year);
        $mes  = $request->get('mes');

        $salesQuery = Sale::where('status', 'concluida')->whereYear('data_venda', $ano);
        if ($mes) {
            $salesQuery->whereMonth('data_venda', $mes);
        }

        $vendas = $salesQuery->with(['vehicle', 'customer'])->latest('data_venda')->get();

        $filename = "relatorio-financeiro-{$ano}" . ($mes ? "-{$mes}" : '') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($vendas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ['Data', 'Veículo', 'Cliente', 'Tipo Pagamento', 'Preço Venda', 'Preço Compra', 'Lucro'], ';');

            foreach ($vendas as $venda) {
                $precoCompra = $venda->vehicle?->preco_compra ?? 0;
                fputcsv($handle, [
                    $venda->data_venda->format('d/m/Y'),
                    $venda->vehicle?->titulo ?? '—',
                    $venda->customer?->nome ?? '—',
                    $venda->tipo_pagamento_label,
                    number_format($venda->preco_venda, 2, ',', '.'),
                    number_format($precoCompra, 2, ',', '.'),
                    number_format($venda->preco_venda - $precoCompra, 2, ',', '.'),
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
