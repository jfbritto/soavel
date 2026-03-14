@extends('adminlte::page')

@section('title', 'Sócios — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-user-tie mr-2"></i>Sócios</h1>
        <button type="button" class="btn btn-sm btn-primary" id="btnNovoSocio">
            <i class="fas fa-plus mr-1"></i>Novo Sócio
        </button>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3">Nome</th>
                        <th class="border-top-0">CPF</th>
                        <th class="border-top-0">Telefone</th>
                        <th class="border-top-0">E-mail</th>
                        <th class="border-top-0 text-center">Veículos</th>
                        <th class="border-top-0" style="width:90px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                    <tr class="partner-row" style="cursor:pointer" data-toggle="collapse" data-target="#vehicles-{{ $partner->id }}">
                        <td class="pl-3 align-middle font-weight-600" style="font-size:.9rem">
                            <i class="fas fa-chevron-right mr-2 text-muted toggle-icon" style="font-size:.65rem;transition:transform .2s"></i>{{ $partner->nome }}
                        </td>
                        <td class="align-middle text-muted" style="font-size:.88rem">{{ $partner->cpf ?? '—' }}</td>
                        <td class="align-middle" style="font-size:.88rem">
                            @if($partner->telefone)
                                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $partner->telefone) }}"
                                   target="_blank" class="text-success" style="white-space:nowrap"
                                   onclick="event.stopPropagation()">
                                    <i class="fab fa-whatsapp mr-1"></i>{{ $partner->telefone }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="align-middle text-muted" style="font-size:.88rem">{{ $partner->email ?? '—' }}</td>
                        <td class="align-middle text-center">
                            @if($partner->vehicles_count > 0)
                                <span class="badge badge-info" style="font-size:.78rem">
                                    <i class="fas fa-handshake mr-1"></i>{{ $partner->vehicles_count }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size:.88rem">—</span>
                            @endif
                        </td>
                        <td class="align-middle text-right pr-3" style="white-space:nowrap" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                                style="font-size:.75rem;padding:2px 8px"
                                title="Editar dados do sócio"
                                data-id="{{ $partner->id }}"
                                data-nome="{{ $partner->nome }}"
                                data-cpf="{{ $partner->cpf }}"
                                data-telefone="{{ $partner->telefone }}"
                                data-email="{{ $partner->email }}"
                                data-observacoes="{{ $partner->observacoes }}"
                                data-url="{{ route('admin.partners.update', $partner) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST"
                                  class="d-inline" data-confirm="Excluir este sócio?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    style="font-size:.75rem;padding:2px 8px;color:#dc3545;border-color:#dee2e6"
                                    title="Excluir este sócio permanentemente">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    {{-- Veículos em sociedade (expandível) --}}
                    @if($partner->vehicles_count > 0)
                    <tr class="collapse" id="vehicles-{{ $partner->id }}">
                        <td colspan="6" class="p-0 border-top-0" style="background:#f8f9fb">
                            <div class="px-3 py-2">
                                <div class="d-flex align-items-center mb-2" style="gap:6px">
                                    <i class="fas fa-car text-muted" style="font-size:.75rem"></i>
                                    <span style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;font-weight:700">
                                        Veículos em sociedade
                                    </span>
                                </div>
                                @foreach($partner->vehicles as $pv)
                                <a href="{{ route('admin.vehicles.show', $pv) }}"
                                   class="d-flex align-items-center mb-2 text-decoration-none partner-vehicle-row"
                                   style="gap:12px;padding:8px 10px;border-radius:8px;background:#fff;border:1px solid #e9ecef;transition:box-shadow .15s">
                                    @if($pv->principalPhoto)
                                        <img src="{{ $pv->principalPhoto->url }}" width="56" height="42"
                                             style="object-fit:cover;border-radius:5px;flex-shrink:0;border:1px solid #e9ecef">
                                    @else
                                        <div style="width:56px;height:42px;background:#f0f0f0;border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid #e9ecef">
                                            <i class="fas fa-car" style="color:#ced4da;font-size:.8rem"></i>
                                        </div>
                                    @endif
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:.88rem;font-weight:600;color:#212529">
                                            {{ trim("{$pv->marca} {$pv->modelo}") }}
                                            <span class="text-muted font-weight-normal">{{ $pv->ano_modelo }}</span>
                                        </div>
                                        <div style="font-size:.78rem;color:#6c757d">
                                            {{ $pv->versao }}
                                        </div>
                                    </div>
                                    <div class="text-right" style="flex-shrink:0">
                                        <div style="font-size:.88rem;font-weight:700;color:#1a7a3c">
                                            R$ {{ number_format($pv->preco, 0, ',', '.') }}
                                        </div>
                                        <div style="font-size:.78rem">
                                            <span class="badge badge-info" style="font-size:.72rem">{{ number_format($pv->pivot->percentual, 1, ',', '') }}%</span>
                                            <span class="badge badge-{{ $pv->status === 'disponivel' ? 'success' : ($pv->status === 'vendido' ? 'danger' : 'warning') }}" style="font-size:.72rem">
                                                {{ ucfirst($pv->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-user-tie fa-2x d-block mb-2" style="color:#dee2e6"></i>
                            Nenhum sócio cadastrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partners->hasPages())
        <div class="card-footer border-top-0" style="background:#fafafa">{{ $partners->links() }}</div>
        @endif
    </div>

</div>

{{-- Modal Novo/Editar Sócio --}}
<div class="modal fade" id="modalSocio" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formSocio" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalTitulo">
                        <i class="fas fa-user-tie mr-2"></i>Novo Sócio
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show py-2">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $e)<li style="font-size:.88rem">{{ $e }}</li>@endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-bold">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="modalNome"
                            class="form-control @error('nome') is-invalid @enderror"
                            value="{{ old('nome') }}" maxlength="150" required>
                        @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">CPF</label>
                                <input type="text" name="cpf" id="modalCpf"
                                    class="form-control @error('cpf') is-invalid @enderror"
                                    value="{{ old('cpf') }}" maxlength="14" placeholder="000.000.000-00">
                                @error('cpf')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Telefone</label>
                                <input type="text" name="telefone" id="modalTelefone"
                                    class="form-control @error('telefone') is-invalid @enderror"
                                    value="{{ old('telefone') }}" maxlength="20" placeholder="(00) 00000-0000">
                                @error('telefone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">E-mail</label>
                        <input type="email" name="email" id="modalEmail"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" maxlength="150">
                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Observações</label>
                        <textarea name="observacoes" id="modalObservacoes" class="form-control" rows="2">{{ old('observacoes') }}</textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i><span id="btnSalvarTxt">Salvar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .partner-row:hover { background-color: #f8f9fa !important; }
    .partner-row[aria-expanded="true"] .toggle-icon { transform: rotate(90deg); }
    .partner-vehicle-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08) !important; }
</style>
@endsection

@section('js')
<script>
(function () {
    var storeUrl = '{{ route('admin.partners.store') }}';

    // ── Máscara CPF ───────────────────────────────────────────
    document.getElementById('modalCpf').addEventListener('input', function () {
        var v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 9) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
        else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
        else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
        this.value = v;
    });

    // ── Máscara Telefone ──────────────────────────────────────
    document.getElementById('modalTelefone').addEventListener('input', function () {
        var v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 10) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
        else if (v.length > 6)  v = '('+v.slice(0,2)+') '+v.slice(2,6)+'-'+v.slice(6);
        else if (v.length > 2)  v = '('+v.slice(0,2)+') '+v.slice(2);
        this.value = v;
    });

    // ── Abrir modal "Novo Sócio" ──────────────────────────────
    document.getElementById('btnNovoSocio').addEventListener('click', function () {
        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-user-tie mr-2"></i>Novo Sócio';
        document.getElementById('btnSalvarTxt').textContent = 'Salvar';
        document.getElementById('formSocio').action = storeUrl;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalNome').value = '';
        document.getElementById('modalCpf').value = '';
        document.getElementById('modalTelefone').value = '';
        document.getElementById('modalEmail').value = '';
        document.getElementById('modalObservacoes').value = '';
        $('#modalSocio').modal('show');
    });

    // ── Abrir modal "Editar Sócio" ────────────────────────────
    document.querySelectorAll('.btn-editar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = this.dataset;
            document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Sócio';
            document.getElementById('btnSalvarTxt').textContent = 'Atualizar';
            document.getElementById('formSocio').action = d.url;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('modalNome').value = d.nome;
            document.getElementById('modalCpf').value = d.cpf || '';
            document.getElementById('modalTelefone').value = d.telefone || '';
            document.getElementById('modalEmail').value = d.email || '';
            document.getElementById('modalObservacoes').value = d.observacoes || '';
            $('#modalSocio').modal('show');
        });
    });

    // ── Reabrir modal se houver erros de validação ────────────
    @if($errors->any())
    $('#modalSocio').modal('show');
    @endif

})();
</script>
@endsection
