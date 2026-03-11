@extends('adminlte::page')

@section('title', 'Leads — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-phone-alt mr-2"></i>Leads / Contatos</h1>
        <a href="{{ route('admin.leads.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i>Novo Lead
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Status summary --}}
    <div class="row mb-3">
        @foreach(['novo' => ['primary','Novos'], 'em_contato' => ['info','Em Contato'], 'convertido' => ['success','Convertidos'], 'perdido' => ['danger','Perdidos']] as $status => [$color, $label])
        <div class="col-md-3 col-sm-6 mb-2">
            <a href="{{ route('admin.leads.index', ['status' => $status]) }}" class="text-decoration-none">
                <div class="info-box mb-0 shadow-sm bg-{{ $color }}">
                    <span class="info-box-icon"><i class="fas fa-phone-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="font-size:.8rem">{{ $label }}</span>
                        <span class="info-box-number" style="font-size:1.4rem;font-weight:700">{{ $counts[$status] }}</span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:8px">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Nome, telefone, e-mail..." value="{{ request('search') }}" style="min-width:200px">
                    <div class="input-group-append">
                        <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <select name="status" class="form-control form-control-sm">
                    <option value="">Todos os status</option>
                    <option value="novo"        {{ (request('status') === 'novo')        ? 'selected' : '' }}>Novo</option>
                    <option value="em_contato"  {{ (request('status') === 'em_contato')  ? 'selected' : '' }}>Em Contato</option>
                    <option value="convertido"  {{ (request('status') === 'convertido')  ? 'selected' : '' }}>Convertido</option>
                    <option value="perdido"     {{ (request('status') === 'perdido')     ? 'selected' : '' }}>Perdido</option>
                </select>
                <select name="origem" class="form-control form-control-sm">
                    <option value="">Todas as origens</option>
                    <option value="site"        {{ (request('origem') === 'site')        ? 'selected' : '' }}>Site</option>
                    <option value="whatsapp"    {{ (request('origem') === 'whatsapp')    ? 'selected' : '' }}>WhatsApp</option>
                    <option value="presencial"  {{ (request('origem') === 'presencial')  ? 'selected' : '' }}>Presencial</option>
                    <option value="indicacao"   {{ (request('origem') === 'indicacao')   ? 'selected' : '' }}>Indicação</option>
                    <option value="instagram"   {{ (request('origem') === 'instagram')   ? 'selected' : '' }}>Instagram</option>
                    <option value="outro"       {{ (request('origem') === 'outro')       ? 'selected' : '' }}>Outro</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
                <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-link text-muted">Limpar</a>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3">Nome</th>
                        <th class="border-top-0">Telefone</th>
                        <th class="border-top-0 d-none d-md-table-cell">Veículo / Interesse</th>
                        <th class="border-top-0 d-none d-lg-table-cell">Origem</th>
                        <th class="border-top-0">Status</th>
                        <th class="border-top-0 d-none d-md-table-cell">Data</th>
                        <th class="border-top-0 d-none d-lg-table-cell">Atribuído</th>
                        <th class="border-top-0" style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="pl-3 align-middle" style="font-size:.88rem">
                            <strong>{{ $lead->nome }}</strong>
                        </td>
                        <td class="align-middle" style="font-size:.88rem">
                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $lead->telefone) }}"
                               target="_blank" class="text-success text-nowrap">
                                <i class="fab fa-whatsapp mr-1"></i>{{ $lead->telefone }}
                            </a>
                        </td>
                        <td class="align-middle d-none d-md-table-cell" style="font-size:.85rem">
                            @if($lead->vehicle?->id)
                                <a href="{{ route('admin.vehicles.show', $lead->vehicle) }}" class="text-dark">
                                    {{ $lead->vehicle->titulo }}
                                </a>
                            @else
                                <span class="text-muted">{{ $lead->interesse ?: '—' }}</span>
                            @endif
                        </td>
                        <td class="align-middle d-none d-lg-table-cell">
                            <span class="badge badge-secondary" style="font-size:.72rem;padding:3px 7px">
                                {{ $lead->origem_label }}
                            </span>
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-{{ $lead->status_color }}" style="font-size:.72rem;padding:3px 8px">
                                {{ $lead->status_label }}
                            </span>
                        </td>
                        <td class="align-middle text-muted d-none d-md-table-cell" style="font-size:.82rem;white-space:nowrap">
                            {{ $lead->created_at->format('d/m/Y') }}
                        </td>
                        <td class="align-middle text-muted d-none d-lg-table-cell" style="font-size:.85rem">
                            {{ $lead->user?->name ?? '—' }}
                        </td>
                        <td class="align-middle text-right pr-3">
                            <a href="{{ route('admin.leads.edit', $lead) }}"
                               class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;padding:2px 10px"
                               title="Abrir e editar este lead">
                                Abrir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-phone-slash fa-2x d-block mb-2" style="color:#dee2e6"></i>
                            Nenhum lead encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
        <div class="card-footer border-top-0" style="background:#fafafa">{{ $leads->links() }}</div>
        @endif
    </div>

</div>
@endsection
