{{-- Um veículo de troca. $i = índice no array trocas[]; $t = valores (old ou vazio) --}}
@php
    $t = $t ?? [];
    $k = "trocas.$i"; // prefixo das chaves de erro
@endphp
<div class="troca-item border rounded p-3 mb-3" style="background:#fffdf5;border-color:#ffe8a1 !important">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="font-weight-bold text-warning"><i class="fas fa-car mr-1"></i>Veículo de troca <span class="troca-numero">{{ is_numeric($i) ? $i + 1 : '' }}</span></span>
        <button type="button" class="btn btn-xs btn-outline-danger btnRemoverTroca" title="Remover este veículo">
            <i class="fas fa-times mr-1"></i>Remover
        </button>
    </div>

    {{-- Valor Avaliado (destaque) --}}
    <div class="row mb-2">
        <div class="col-md-4">
            <div class="form-group mb-0">
                <label for="troca_valor_display_{{ $i }}"><i class="fas fa-tag mr-1 text-warning"></i>Valor Avaliado na Troca *</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold text-warning">R$</span></div>
                    <input type="text" id="troca_valor_display_{{ $i }}"
                        class="form-control font-weight-bold troca-valor-display @error("$k.valor_troca") is-invalid @enderror"
                        placeholder="0,00"
                        value="{{ isset($t['valor_troca']) && $t['valor_troca'] !== '' ? number_format((float) $t['valor_troca'], 2, ',', '.') : '' }}"
                        autocomplete="off">
                </div>
                <input type="hidden" name="trocas[{{ $i }}][valor_troca]" class="troca-valor" value="{{ $t['valor_troca'] ?? '' }}">
                @error("$k.valor_troca")<span class="text-danger small">{{ $message }}</span>@enderror
                <small class="text-muted">Quanto este veículo vale na troca</small>
            </div>
        </div>
    </div>

    <hr class="mt-2 mb-3">
    <p class="text-muted font-weight-bold mb-2 small text-uppercase"><i class="fas fa-car mr-1"></i>Identificação</p>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="troca_marca_{{ $i }}">Marca *</label>
                <input type="text" name="trocas[{{ $i }}][marca]" id="troca_marca_{{ $i }}"
                    class="form-control @error("$k.marca") is-invalid @enderror"
                    value="{{ $t['marca'] ?? '' }}" placeholder="Ex: Chevrolet">
                @error("$k.marca")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label for="troca_modelo_{{ $i }}">Modelo *</label>
                <input type="text" name="trocas[{{ $i }}][modelo]" id="troca_modelo_{{ $i }}"
                    class="form-control @error("$k.modelo") is-invalid @enderror"
                    value="{{ $t['modelo'] ?? '' }}" placeholder="Ex: Onix LT 1.0">
                @error("$k.modelo")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="troca_cor_{{ $i }}">Cor *</label>
                <input type="text" name="trocas[{{ $i }}][cor]" id="troca_cor_{{ $i }}"
                    class="form-control @error("$k.cor") is-invalid @enderror"
                    value="{{ $t['cor'] ?? '' }}" placeholder="Ex: Prata">
                @error("$k.cor")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="troca_versao_{{ $i }}">Versão</label>
                <input type="text" name="trocas[{{ $i }}][versao]" id="troca_versao_{{ $i }}"
                    class="form-control"
                    value="{{ $t['versao'] ?? '' }}" placeholder="Opcional">
            </div>
        </div>
    </div>

    <p class="text-muted font-weight-bold mb-2 small text-uppercase"><i class="fas fa-cog mr-1"></i>Dados Técnicos</p>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label for="troca_ano_fabricacao_{{ $i }}">Ano Fab. *</label>
                <input type="text" name="trocas[{{ $i }}][ano_fabricacao]" id="troca_ano_fabricacao_{{ $i }}"
                    class="form-control mask-ano @error("$k.ano_fabricacao") is-invalid @enderror"
                    value="{{ $t['ano_fabricacao'] ?? '' }}" placeholder="{{ date('Y') }}"
                    maxlength="4" inputmode="numeric">
                @error("$k.ano_fabricacao")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="troca_ano_modelo_{{ $i }}">Ano Mod. *</label>
                <input type="text" name="trocas[{{ $i }}][ano_modelo]" id="troca_ano_modelo_{{ $i }}"
                    class="form-control mask-ano @error("$k.ano_modelo") is-invalid @enderror"
                    value="{{ $t['ano_modelo'] ?? '' }}" placeholder="{{ date('Y') + 1 }}"
                    maxlength="4" inputmode="numeric">
                @error("$k.ano_modelo")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="troca_km_display_{{ $i }}">Quilometragem *</label>
                <div class="input-group">
                    <input type="text" id="troca_km_display_{{ $i }}"
                        class="form-control troca-km-display @error("$k.km") is-invalid @enderror"
                        value="{{ isset($t['km']) && $t['km'] !== '' ? number_format((int) $t['km'], 0, ',', '.') : '' }}"
                        placeholder="45.000" autocomplete="off" inputmode="numeric">
                    <div class="input-group-append"><span class="input-group-text">km</span></div>
                </div>
                <input type="hidden" name="trocas[{{ $i }}][km]" class="troca-km" value="{{ $t['km'] ?? '' }}">
                @error("$k.km")<span class="text-danger small">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="troca_categoria_{{ $i }}">Categoria *</label>
                <select name="trocas[{{ $i }}][categoria]" id="troca_categoria_{{ $i }}"
                    class="form-control @error("$k.categoria") is-invalid @enderror">
                    <option value="">— Selec. —</option>
                    @foreach(['hatch'=>'Hatch','sedan'=>'Sedan','suv'=>'SUV','pickup'=>'Pickup','van'=>'Van','esportivo'=>'Esportivo','outro'=>'Outro'] as $val => $lbl)
                        <option value="{{ $val }}" {{ (($t['categoria'] ?? '') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error("$k.categoria")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="troca_combustivel_{{ $i }}">Combustível *</label>
                <select name="trocas[{{ $i }}][combustivel]" id="troca_combustivel_{{ $i }}"
                    class="form-control @error("$k.combustivel") is-invalid @enderror">
                    <option value="">— Selecionar —</option>
                    @foreach(['flex'=>'Flex','gasolina'=>'Gasolina','etanol'=>'Etanol','diesel'=>'Diesel','gnv'=>'GNV','hibrido'=>'Híbrido','eletrico'=>'Elétrico'] as $val => $lbl)
                        <option value="{{ $val }}" {{ (($t['combustivel'] ?? '') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error("$k.combustivel")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-0">
                <label for="troca_transmissao_{{ $i }}">Transmissão *</label>
                <select name="trocas[{{ $i }}][transmissao]" id="troca_transmissao_{{ $i }}"
                    class="form-control @error("$k.transmissao") is-invalid @enderror">
                    <option value="">— Selecionar —</option>
                    @foreach(['manual'=>'Manual','automatico'=>'Automático','cvt'=>'CVT','semi_automatico'=>'Semi-automático'] as $val => $lbl)
                        <option value="{{ $val }}" {{ (($t['transmissao'] ?? '') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error("$k.transmissao")<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>
