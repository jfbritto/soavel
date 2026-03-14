@extends('adminlte::page')

@section('title', 'Novo Veículo — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus mr-2"></i>Novo Veículo</h1>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.vehicles.store') }}" method="POST">
        @csrf

        @include('admin.vehicles._form', ['vehicle' => null, 'featuresByCategory' => $featuresByCategory, 'currentFeatures' => []])

        <div class="d-flex justify-content-end mt-3 pb-4" style="gap:8px">
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i>Salvar Veículo
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
(function () {
    function mascaraMoeda(display, hidden) {
        display.addEventListener('input', function () {
            var raw = this.value.replace(/\D/g, '');
            if (!raw) { this.value = ''; hidden.value = ''; return; }
            var num = parseInt(raw, 10) / 100;
            hidden.value = num.toFixed(2);
            this.value = num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        });
    }

    mascaraMoeda(document.getElementById('precoDisplay'),       document.getElementById('precoHidden'));
    mascaraMoeda(document.getElementById('precoCompraDisplay'), document.getElementById('precoCompraHidden'));

    var kmDisplay = document.getElementById('kmDisplay');
    var kmHidden  = document.getElementById('kmHidden');

    kmDisplay.addEventListener('input', function () {
        var raw = this.value.replace(/\D/g, '');
        if (!raw) { this.value = ''; kmHidden.value = ''; return; }
        var num = parseInt(raw, 10);
        kmHidden.value = num;
        this.value = num.toLocaleString('pt-BR');
    });
})();
</script>

@include('admin.vehicles._fipe_script')
@endsection
