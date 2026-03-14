@extends('adminlte::page')
@section('title', 'Editar Cliente — ' . config('adminlte.title'))
@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-edit mr-2"></i>Editar: {{ $customer->nome }}</h1>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
@endsection
@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.customers._form', ['customer' => $customer])
        <div class="card-footer">
            <button type="submit" class="btn btn-primary" title="Salvar as alterações do cliente"><i class="fas fa-save mr-1"></i>Atualizar</button>
            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-default ml-2" title="Descartar alterações e voltar">Cancelar</a>
        </div>
    </form>
</div>
@endsection
