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

    {{-- Alerta de fatura atrasada --}}
    <?php if(auth()->check()): ?>
        <?php
            $billingStatus = \App\Models\Setting::get('billing_status');
            $billingDueDate = \App\Models\Setting::get('billing_due_date');
            $billingOverdue = false;
            $billingDaysLate = 0;
            if ($billingDueDate) {
                $due = \Carbon\Carbon::parse($billingDueDate)->startOfDay();
                $billingDaysLate = (int) $due->diffInDays(now()->startOfDay(), false);
                $billingOverdue = $billingStatus === 'overdue' || $billingDaysLate > 0;
            }
        ?>
        @if($billingOverdue)
            <div class="mx-3 mt-2">
                <div class="alert alert-danger alert-dismissible fade show mb-0 shadow-sm" role="alert" style="border-left:4px solid #c0392b">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-3" style="font-size:1.5rem"></i>
                        <div>
                            <strong>Fatura em atraso!</strong>
                            Sua mensalidade com vencimento em {{ \Carbon\Carbon::parse($billingDueDate)->format('d/m/Y') }} está pendente.
                            <a href="{{ route('admin.billing.index') }}" class="alert-link ml-1">Ver detalhes e pagar</a>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            </div>
        @elseif($billingDueDate && $billingStatus === 'pending' && $billingDaysLate >= -5)
            <div class="mx-3 mt-2">
                <div class="alert alert-warning alert-dismissible fade show mb-0 shadow-sm" role="alert" style="border-left:4px solid #f39c12">
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
            </div>
        @endif
    <?php endif; ?>

    {{-- Main Content --}}
    <div class="content">
        <div class="{{ config('adminlte.classes_content') ?: $def_container_class }}">
            @yield('content')
        </div>
    </div>

</div>
