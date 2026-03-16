@extends('adminlte::page')

@section('title', 'Clientes — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-users mr-2"></i>Clientes</h1>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i>Novo Cliente
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control"
                        placeholder="Nome, CPF, telefone ou e-mail..."
                        value="{{ request('search') }}" style="min-width:260px">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        @if(request('search'))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Limpar</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3">Nome</th>
                        <th class="border-top-0 d-none d-lg-table-cell">CPF</th>
                        <th class="border-top-0">Telefone</th>
                        <th class="border-top-0 d-none d-md-table-cell">E-mail</th>
                        <th class="border-top-0 d-none d-md-table-cell">Cidade</th>
                        <th class="border-top-0 text-center d-none d-lg-table-cell">Compras</th>
                        <th class="border-top-0" style="width:110px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="pl-3 align-middle">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="font-weight-600 text-dark" style="font-size:.9rem">
                                {{ $customer->nome }}
                            </a>
                        </td>
                        <td class="align-middle text-muted d-none d-lg-table-cell" style="font-size:.88rem">{{ $customer->cpf ?? '—' }}</td>
                        <td class="align-middle" style="font-size:.88rem">
                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $customer->telefone) }}"
                               target="_blank" class="text-success" style="white-space:nowrap">
                                <i class="fab fa-whatsapp mr-1"></i>{{ $customer->telefone }}
                            </a>
                        </td>
                        <td class="align-middle text-muted d-none d-md-table-cell" style="font-size:.88rem">{{ $customer->email ?? '—' }}</td>
                        <td class="align-middle text-muted d-none d-md-table-cell" style="font-size:.88rem">
                            {{ $customer->cidade ? $customer->cidade.'/'.$customer->estado : '—' }}
                        </td>
                        <td class="align-middle text-center d-none d-lg-table-cell">
                            @if($customer->sales_count > 0)
                                <span class="badge badge-light border" style="font-size:.78rem">{{ $customer->sales_count }}</span>
                            @else
                                <span class="text-muted" style="font-size:.88rem">—</span>
                            @endif
                        </td>
                        <td class="align-middle text-right pr-3" style="white-space:nowrap">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;padding:2px 8px"
                               title="Ver perfil e histórico do cliente">Ver</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}"
                               class="btn btn-sm btn-outline-secondary ml-1" style="font-size:.75rem;padding:2px 8px"
                               title="Editar dados do cliente">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x d-block mb-2" style="color:#dee2e6"></i>
                            Nenhum cliente encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-3" style="background:#f8f9fa;border-top:1px solid #eee">
            <small class="text-muted">{{ $customers->total() }} {{ $customers->total() === 1 ? 'registro' : 'registros' }}</small>
            @if($customers->hasPages()) {{ $customers->withQueryString()->links() }} @endif
        </div>
    </div>
</div>
@endsection
