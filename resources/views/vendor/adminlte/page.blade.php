@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @include('adminlte::partials.footer.footer')

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>

    {{-- Toast Notifications --}}
    <div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:320px"></div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    <style>
        .soavel-toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
            margin-bottom: 10px;
            font-size: 15px;
            font-weight: 500;
            opacity: 0;
            transform: translateX(40px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            color: #fff;
        }
        .soavel-toast.show { opacity: 1; transform: translateX(0); }
        .soavel-toast.hide { opacity: 0; transform: translateX(40px); }
        .soavel-toast-success { background: #28a745; }
        .soavel-toast-error   { background: #dc3545; }
        .soavel-toast-warning { background: #e6a817; color: #333; }
        .soavel-toast-info    { background: #17a2b8; }
        .soavel-toast-icon { font-size: 20px; flex-shrink: 0; margin-top: 1px; }
        .soavel-toast-close {
            margin-left: auto;
            background: none;
            border: none;
            color: inherit;
            opacity: 0.7;
            font-size: 18px;
            cursor: pointer;
            padding: 0 0 0 8px;
            line-height: 1;
        }
        .soavel-toast-close:hover { opacity: 1; }
    </style>

    <script>
    function showToast(message, type) {
        const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `soavel-toast soavel-toast-${type}`;
        toast.innerHTML = `
            <span class="soavel-toast-icon"><i class="fas ${icons[type] || icons.info}"></i></span>
            <span>${message}</span>
            <button class="soavel-toast-close" onclick="removeToast(this.parentElement)">&times;</button>
        `;
        container.appendChild(toast);
        requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('show'); }); });
        setTimeout(() => removeToast(toast), 5000);
    }
    function removeToast(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 350);
    }
    @if(session('success'))  showToast(@json(session('success')), 'success'); @endif
    @if(session('error'))    showToast(@json(session('error')),   'error');   @endif
    @if(session('warning'))  showToast(@json(session('warning')), 'warning'); @endif
    @if(session('info'))     showToast(@json(session('info')),    'info');    @endif

    // SweetAlert2 — intercepta todos os forms com data-confirm
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const msg  = form.getAttribute('data-confirm');
        if (!msg) return;
        e.preventDefault();
        if (typeof Swal === 'undefined') {
            if (confirm(msg)) { form.removeAttribute('data-confirm'); form.submit(); }
            return;
        }
        Swal.fire({
            title: 'Tem certeza?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                form.removeAttribute('data-confirm');
                form.submit();
            }
        });
    });
    </script>
@stop
