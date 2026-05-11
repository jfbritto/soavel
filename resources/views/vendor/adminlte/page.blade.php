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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function showToast(message, type) {
        const iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: iconMap[type] || 'info',
            title: message,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: function(toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
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
        const methodInput = form.querySelector('input[name="_method"]');
        const httpMethod = (methodInput?.value || form.method || 'POST').toUpperCase();
        const isDestructive = httpMethod === 'DELETE'
            || form.hasAttribute('data-confirm-destructive');
        const title = form.getAttribute('data-confirm-title')
            || (isDestructive ? 'Tem certeza?' : 'Confirmar ação');
        const confirmText = form.getAttribute('data-confirm-button')
            || (isDestructive ? 'Sim, excluir' : 'Confirmar');
        const confirmColor = form.getAttribute('data-confirm-color')
            || (isDestructive ? '#d33' : '#3085d6');
        const icon = form.getAttribute('data-confirm-icon')
            || (isDestructive ? 'warning' : 'question');
        Swal.fire({
            title: title,
            text: msg,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
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
