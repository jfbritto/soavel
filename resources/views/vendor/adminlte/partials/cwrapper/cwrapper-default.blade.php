@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@if($layoutHelper->isLayoutTopnavEnabled())
    @php( $def_container_class = 'container' )
@else
    @php( $def_container_class = 'container-fluid' )
@endif

{{-- Default Content Wrapper --}}
<div class="content-wrapper {{ config('adminlte.classes_content_wrapper', '') }}">

    {{-- Content Header --}}
    @hasSection('content_header')
        <div class="content-header">
            <div class="{{ config('adminlte.classes_content_header') ?: $def_container_class }}">
                @yield('content_header')
            </div>
        </div>
    @endif

    {{-- Alertas do sistema --}}
    <?php if(auth()->check()): ?>
        <?php
            $siteIsSuspended = \App\Models\Setting::get('suspended') === 'true';
            $billingStatus = \App\Models\Setting::get('billing_status');
            $billingDueDate = \App\Models\Setting::get('billing_due_date');
            $billingOverdue = false;
            $billingDaysLate = 0;
            if ($billingDueDate) {
                $currentDuePaid = \App\Models\BillingHistory::where('environment', 'production')
                    ->whereDate('due_date', \Carbon\Carbon::parse($billingDueDate)->toDateString())
                    ->whereIn('status', ['confirmed', 'received', 'RECEIVED', 'CONFIRMED'])
                    ->exists();

                if ($currentDuePaid) {
                    $nextPending = \App\Models\BillingHistory::where('environment', 'production')
                        ->whereIn('status', ['pending', 'overdue', 'awaiting_payment', 'PENDING', 'OVERDUE', 'AWAITING_PAYMENT'])
                        ->orderBy('due_date', 'asc')
                        ->first();
                    if ($nextPending) {
                        $billingDueDate = $nextPending->due_date->toDateString();
                        $billingStatus = strtolower($nextPending->status);
                    } else {
                        $billingDueDate = null;
                    }
                }

                if ($billingDueDate) {
                    $due = \Carbon\Carbon::parse($billingDueDate)->startOfDay();
                    $billingDaysLate = (int) $due->diffInDays(now()->startOfDay(), false);
                    $billingOverdue = $billingStatus === 'overdue' || $billingDaysLate > 0;
                }
            }
        ?>
    <?php endif; ?>

    {{-- Main Content --}}
    <div class="content">
        <div class="{{ config('adminlte.classes_content') ?: $def_container_class }}">
            @if(isset($siteIsSuspended) && $siteIsSuspended)
                <div class="alert alert-dark shadow-sm" role="alert" style="border-left:4px solid #2c3e50; border-radius:6px; background:#2c3e50; color:#fff">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-ban mr-3" style="font-size:1.5rem"></i>
                        <div>
                            <strong>Site suspenso!</strong>
                            Seu site está temporariamente fora do ar para os visitantes devido a pendências financeiras.
                            <a href="{{ route('admin.billing.index') }}" class="ml-1" style="color:#f1c40f; text-decoration:underline">Regularizar agora</a>
                        </div>
                    </div>
                </div>
            @endif
            @if($billingOverdue)
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left:4px solid #c0392b; border-radius:6px">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-3" style="font-size:1.4rem"></i>
                        <div>
                            <strong>Fatura em atraso!</strong>
                            Sua mensalidade com vencimento em {{ \Carbon\Carbon::parse($billingDueDate)->format('d/m/Y') }} está pendente.
                            <a href="{{ route('admin.billing.index') }}" class="alert-link ml-1">Ver detalhes e pagar</a>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @elseif($billingDueDate && $billingStatus === 'pending' && $billingDaysLate >= -5)
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="border-left:4px solid #f39c12; border-radius:6px">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock mr-3" style="font-size:1.3rem"></i>
                        <div>
                            <strong>Fatura próxima do vencimento!</strong>
                            Sua mensalidade vence em {{ \Carbon\Carbon::parse($billingDueDate)->format('d/m/Y') }}.
                            <a href="{{ route('admin.billing.index') }}" class="alert-link ml-1">Ver detalhes</a>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

</div>
