@extends('adminlte::page')

@section('title', 'Minha Assinatura — ' . config('adminlte.title'))

@section('content_header')
    <h1 class="m-0"><i class="fas fa-file-invoice mr-2"></i>Minha Assinatura</h1>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Card Status da Assinatura --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-crown mr-1 text-warning"></i>Plano HelpFlux Veículos
                    </h5>
                </div>
                <div class="card-body">
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
                        <span class="font-weight-bold" style="font-size:1.2rem;color:#27ae60">
                            R$ {{ number_format((float)$billing['amount'], 2, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    <hr>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Sua assinatura inclui acesso completo ao sistema de gestão, site da loja, e suporte técnico.
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Próxima Fatura --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i>Próxima Fatura
                    </h5>
                </div>
                <div class="card-body">
                    @if($billing['due_date'])
                        @php
                            $dueDate = \Carbon\Carbon::parse($billing['due_date']);
                            $today = now()->startOfDay();
                            $daysUntil = $today->diffInDays($dueDate, false);
                            $isPast = $daysUntil < 0;
                        @endphp

                        <div class="text-center mb-3">
                            <div style="font-size:2rem;font-weight:700;color:{{ $isPast ? '#e74c3c' : '#2c3e50' }}">
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

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Valor</span>
                            <span class="font-weight-bold">R$ {{ number_format((float)$billing['amount'], 2, ',', '.') }}</span>
                        </div>

                        @if($billing['billing_type'])
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Forma de pagamento</span>
                            <span>
                                @switch($billing['billing_type'])
                                    @case('BOLETO')
                                        <i class="fas fa-barcode mr-1"></i>Boleto
                                        @break
                                    @case('PIX')
                                        <i class="fas fa-qrcode mr-1"></i>Pix
                                        @break
                                    @case('CREDIT_CARD')
                                        <i class="fas fa-credit-card mr-1"></i>Cartão de Crédito
                                        @break
                                    @default
                                        <i class="fas fa-money-bill mr-1"></i>{{ $billing['billing_type'] }}
                                @endswitch
                            </span>
                        </div>
                        @endif

                        @if($billing['status'] === 'pending' || $billing['status'] === 'overdue')
                        <hr>
                        <div class="text-center">
                            @if($billing['invoice_url'])
                                <a href="{{ $billing['invoice_url'] }}" target="_blank"
                                    class="btn btn-success btn-lg btn-block">
                                    <i class="fas fa-external-link-alt mr-2"></i>Pagar Fatura
                                </a>
                            @else
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    O link de pagamento será disponibilizado em breve.
                                </p>
                            @endif
                        </div>
                        @elseif($billing['status'] === 'confirmed' || $billing['status'] === 'received')
                        <hr>
                        <div class="text-center">
                            <span class="text-success font-weight-bold">
                                <i class="fas fa-check-circle mr-1"></i>Fatura paga
                            </span>
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
    </div>

    {{-- Informações de contato --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <i class="fas fa-headset text-primary mr-3" style="font-size:1.5rem"></i>
                <div>
                    <strong>Precisa de ajuda?</strong>
                    <p class="mb-0 text-muted small">
                        Entre em contato com o suporte HelpFlux pelo WhatsApp ou e-mail para dúvidas sobre sua assinatura.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
