@extends('adminlte::page')

@section('title', 'Minha Assinatura — ' . config('adminlte.title'))

@section('content_header')
    <h1 class="m-0"><i class="fas fa-file-invoice mr-2"></i>Minha Assinatura</h1>
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        {{-- Card Plano --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-crown mr-1 text-warning"></i>Plano HelpFlux Veículos
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Status</span>
                        @if($billing['subscription_status'] === 'active')
                            <span class="badge badge-success px-3 py-2" style="font-size:.85rem">
                                <i class="fas fa-check-circle mr-1"></i>Ativa
                            </span>
                        @else
                            <span class="badge badge-secondary px-3 py-2" style="font-size:.85rem">
                                <i class="fas fa-times-circle mr-1"></i>Inativa
                            </span>
                        @endif
                    </div>

                    @if($billing['amount'])
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Valor mensal</span>
                        <span class="font-weight-bold" style="font-size:1.3rem;color:#27ae60">
                            R$ {{ number_format((float)$billing['amount'], 2, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    <hr>

                    <div class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i><small>Sistema de gestão completo</small>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i><small>Site da loja personalizado</small>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i><small>Cadastro ilimitado de veículos</small>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i><small>Gestão de leads e clientes</small>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i><small>Relatórios financeiros</small>
                    </div>
                    <div class="mb-0">
                        <i class="fas fa-check text-success mr-2"></i><small>Suporte técnico</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Próxima Fatura --}}
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i>Próxima Fatura
                    </h5>
                </div>
                <div class="card-body pt-0">
                    @if($billing['subscription_status'] !== 'active' && !in_array($billing['status'], ['pending', 'overdue']))
                        <div class="text-center py-4">
                            <i class="fas fa-receipt text-muted" style="font-size:3rem"></i>
                            <p class="text-muted mt-3 mb-0">Assinatura inativa. Nenhuma fatura pendente.</p>
                        </div>
                    @elseif($billing['due_date'])
                        @php
                            $dueDate = \Carbon\Carbon::parse($billing['due_date']);
                            $today = now()->startOfDay();
                            $daysUntil = (int) $today->diffInDays($dueDate, false);
                            $isPast = $daysUntil < 0;
                        @endphp

                        <div class="text-center mb-3">
                            <div style="font-size:2.2rem;font-weight:700;color:{{ $isPast ? '#e74c3c' : '#2c3e50' }}">
                                {{ $dueDate->format('d/m/Y') }}
                            </div>
                            <div class="mt-1">
                                @if($isPast)
                                    <span class="badge badge-danger px-3 py-1" style="font-size:.8rem">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Vencida há {{ abs($daysUntil) }} {{ abs($daysUntil) == 1 ? 'dia' : 'dias' }}
                                    </span>
                                @elseif($daysUntil == 0)
                                    <span class="badge badge-warning px-3 py-1" style="font-size:.8rem">
                                        <i class="fas fa-clock mr-1"></i>Vence hoje
                                    </span>
                                @elseif($daysUntil <= 5)
                                    <span class="badge badge-warning px-3 py-1" style="font-size:.8rem">
                                        <i class="fas fa-clock mr-1"></i>Vence em {{ $daysUntil }} {{ $daysUntil == 1 ? 'dia' : 'dias' }}
                                    </span>
                                @else
                                    <span class="badge badge-info px-3 py-1" style="font-size:.8rem">
                                        <i class="fas fa-calendar mr-1"></i>Faltam {{ $daysUntil }} dias
                                    </span>
                                @endif
                            </div>
                        </div>

                        <table class="table table-sm table-borderless mb-3">
                            <tr>
                                <td class="text-muted pl-0">Valor</td>
                                <td class="text-right font-weight-bold pr-0">R$ {{ number_format((float)$billing['amount'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Situação</td>
                                <td class="text-right pr-0">
                                    @switch($billing['status'])
                                        @case('pending')
                                            <span class="badge badge-warning">Aguardando pagamento</span>
                                            @break
                                        @case('overdue')
                                            <span class="badge badge-danger">Vencida</span>
                                            @break
                                        @case('confirmed')
                                        @case('received')
                                            <span class="badge badge-success">Paga</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">Pendente</span>
                                    @endswitch
                                </td>
                            </tr>
                            @if($billing['billing_type'] && $billing['billing_type'] !== 'UNDEFINED')
                            <tr>
                                <td class="text-muted pl-0">Forma de pagamento</td>
                                <td class="text-right pr-0">
                                    @switch($billing['billing_type'])
                                        @case('BOLETO')
                                            <i class="fas fa-barcode mr-1"></i>Boleto
                                            @break
                                        @case('PIX')
                                            <i class="fas fa-qrcode mr-1"></i>Pix
                                            @break
                                        @case('CREDIT_CARD')
                                            <i class="fas fa-credit-card mr-1"></i>Cartão
                                            @break
                                        @default
                                            {{ $billing['billing_type'] }}
                                    @endswitch
                                </td>
                            </tr>
                            @endif
                        </table>

                        {{-- Botão pagar ou status pago --}}
                        @if(in_array($billing['status'], ['pending', 'overdue', 'inactive']))
                            @if($billing['invoice_url'])
                                <a href="{{ $billing['invoice_url'] }}" target="_blank"
                                    class="btn btn-success btn-lg btn-block shadow-sm">
                                    <i class="fas fa-external-link-alt mr-2"></i>Pagar Fatura
                                </a>
                                <p class="text-center text-muted small mt-2 mb-0">
                                    Você pode pagar via <strong>Boleto</strong>, <strong>Pix</strong> ou <strong>Cartão de Crédito</strong>
                                </p>
                            @else
                                <div class="alert alert-info mb-0 text-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    O link de pagamento será disponibilizado em breve pelo sistema de cobrança.
                                </div>
                            @endif
                        @elseif(in_array($billing['status'], ['confirmed', 'received']))
                            <div class="text-center py-2">
                                <i class="fas fa-check-circle text-success" style="font-size:2rem"></i>
                                <p class="font-weight-bold text-success mt-2 mb-0">Fatura paga!</p>
                                <p class="text-muted small mb-0">A próxima cobrança será gerada automaticamente.</p>
                            </div>
                        @endif

                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt text-muted" style="font-size:3rem"></i>
                            <p class="text-muted mt-3 mb-0">Nenhuma fatura disponível no momento.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card Suporte --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-headset mr-1 text-primary"></i>Suporte
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <p class="text-muted small">Dúvidas sobre cobrança ou precisa de ajuda técnica?</p>

                    <a href="https://wa.me/5528999743099?text=Olá! Preciso de ajuda com minha assinatura HelpFlux Veículos."
                        target="_blank" class="btn btn-success btn-block mb-2">
                        <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                    </a>

                    <a href="mailto:jf.britto@hotmail.com?subject=Suporte HelpFlux Veículos"
                        class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-envelope mr-1"></i>E-mail
                    </a>

                    <hr>

                    <div class="text-muted small">
                        <div class="mb-1"><i class="fas fa-clock mr-1"></i>Seg a Sex, 9h às 18h</div>
                        <div><i class="fas fa-phone mr-1"></i>(28) 99974-3099</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Histórico de Faturas --}}
    @if($history->count())
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-history mr-1 text-secondary"></i>Histórico de Faturas
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Situação</th>
                                    <th>Forma de Pagamento</th>
                                    <th>Pago em</th>
                                    <th class="text-center">Fatura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $item)
                                <tr>
                                    <td>{{ $item->due_date->format('d/m/Y') }}</td>
                                    <td class="font-weight-bold">R$ {{ number_format($item->amount, 2, ',', '.') }}</td>
                                    <td>
                                        @switch($item->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pendente</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge badge-danger">Vencida</span>
                                                @break
                                            @case('confirmed')
                                            @case('received')
                                                <span class="badge badge-success">Paga</span>
                                                @break
                                            @case('refunded')
                                                <span class="badge badge-info">Estornada</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $item->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($item->billing_type && $item->billing_type !== 'UNDEFINED')
                                            @switch($item->billing_type)
                                                @case('BOLETO')
                                                    <i class="fas fa-barcode mr-1"></i>Boleto
                                                    @break
                                                @case('PIX')
                                                    <i class="fas fa-qrcode mr-1"></i>Pix
                                                    @break
                                                @case('CREDIT_CARD')
                                                    <i class="fas fa-credit-card mr-1"></i>Cartão
                                                    @break
                                                @default
                                                    {{ $item->billing_type }}
                                            @endswitch
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->paid_at)
                                            {{ $item->paid_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->invoice_url && in_array($item->status, ['pending', 'overdue']))
                                            <a href="{{ $item->invoice_url }}" target="_blank" class="btn btn-sm btn-success">
                                                <i class="fas fa-external-link-alt mr-1"></i>Pagar
                                            </a>
                                        @elseif($item->invoice_url)
                                            <a href="{{ $item->invoice_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye mr-1"></i>Ver
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
