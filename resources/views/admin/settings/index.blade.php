@extends('adminlte::page')

@section('title', 'Configurações — Soavel')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
.cropper-container { max-height: 420px; }
#cropperPreview { width: 160px; height: 80px; overflow: hidden; border: 1px solid #dee2e6; border-radius: 4px; background: repeating-conic-gradient(#e0e0e0 0% 25%, #fff 0% 50%) 0 0 / 12px 12px; }
</style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-cog mr-2"></i>Configurações do Sistema</h1>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card card-primary card-tabs shadow-sm">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-identidade" role="tab">
                            <i class="fas fa-palette mr-1"></i>Identidade
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-empresa" role="tab">
                            <i class="fas fa-building mr-1"></i>Empresa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-contato" role="tab">
                            <i class="fas fa-phone mr-1"></i>Contato
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-site" role="tab">
                            <i class="fas fa-globe mr-1"></i>Site Público
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="settingsTabContent">

                    {{-- ── Identidade Visual ──────────────────────────── --}}
                    <div class="tab-pane fade show active" id="tab-identidade" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Nome do Sistema *</label>
                                    <input type="text" name="nome_sistema" class="form-control"
                                        value="{{ $settings['nome_sistema']->value ?? 'Soavel Veículos' }}"
                                        placeholder="Ex: Soavel Veículos">
                                    <small class="text-muted">Aparece no título das páginas e na barra lateral.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Slogan</label>
                                    <input type="text" name="slogan" class="form-control"
                                        value="{{ $settings['slogan']->value ?? '' }}"
                                        placeholder="Ex: Seu próximo carro está aqui">
                                    <small class="text-muted">Exibido na home do site público.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Logo</label>

                                    {{-- Preview logo atual ou recortada --}}
                                    <div id="logoPreviewWrap" class="mb-2" style="{{ empty($settings['logo_path']->value) ? 'display:none' : '' }}">
                                        <div class="d-flex align-items-center" style="gap:8px">
                                            <img id="logoPreviewImg"
                                                 src="{{ !empty($settings['logo_path']->value) ? asset('storage/' . $settings['logo_path']->value) : '' }}"
                                                 alt="Logo"
                                                 style="max-height:60px;border-radius:4px;border:1px solid #dee2e6;padding:4px;background:#fff">
                                            <button type="button" id="btnRemoveLogo" class="btn btn-sm btn-outline-danger" title="Remover logo e voltar ao padrão" style="{{ empty($settings['logo_path']->value) ? 'display:none' : '' }}">
                                                <i class="fas fa-trash mr-1"></i>Remover
                                            </button>
                                        </div>
                                        <small class="d-block text-muted mt-1" id="logoPreviewLabel">
                                            {{ !empty($settings['logo_path']->value) ? 'Logo atual — envie um novo para substituir.' : '' }}
                                        </small>
                                    </div>
                                    <input type="hidden" name="remove_logo" id="remove_logo" value="0">

                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="logo_path" name="logo_path" accept="image/*">
                                        <label class="custom-file-label" for="logo_path" style="font-size:.9rem">Selecionar imagem...</label>
                                    </div>
                                    @error('logo_path')<span class="text-danger" style="font-size:.82rem">{{ $message }}</span>@enderror
                                    <small class="text-muted">JPG, PNG, WebP · máx. 2 MB · Recomendado: fundo transparente (PNG)</small>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Favicon <small class="text-muted font-weight-normal">(ícone da aba do navegador)</small></label>

                                    {{-- Preview favicon: sempre visível (padrão ou cadastrado) --}}
                                    <div id="faviconPreviewWrap" class="mb-2">
                                        <div class="d-flex align-items-center" style="gap:8px">
                                            <img id="faviconPreviewImg"
                                                 src="{{ !empty($settings['favicon_path']->value) ? asset('storage/' . $settings['favicon_path']->value) : asset('img/default-favicon.svg') }}"
                                                 alt="Favicon"
                                                 style="width:48px;height:48px;object-fit:contain;border-radius:6px;border:1px solid #dee2e6;padding:4px;background:#fff">
                                            <button type="button" id="btnRemoveFavicon" class="btn btn-sm btn-outline-danger" title="Remover favicon e voltar ao padrão" style="{{ empty($settings['favicon_path']->value) ? 'display:none' : '' }}">
                                                <i class="fas fa-trash mr-1"></i>Remover
                                            </button>
                                        </div>
                                        <small class="d-block text-muted mt-1" id="faviconPreviewLabel">
                                            {{ !empty($settings['favicon_path']->value) ? 'Favicon atual — envie um novo para substituir.' : 'Ícone padrão — envie um personalizado.' }}
                                        </small>
                                    </div>
                                    <input type="hidden" name="remove_favicon" id="remove_favicon" value="0">

                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="favicon_path" name="favicon_path" accept="image/*">
                                        <label class="custom-file-label" for="favicon_path" style="font-size:.9rem">Selecionar ícone...</label>
                                    </div>
                                    @error('favicon_path')<span class="text-danger" style="font-size:.82rem">{{ $message }}</span>@enderror
                                    <small class="text-muted">Imagem quadrada · JPG, PNG, SVG · máx. 512×512px</small>
                                </div>
                            </div>
                        </div>

                        {{-- ── Cor da Marca ──────────────────────────────── --}}
                        <div class="row mt-2">
                            <div class="col-12">
                                <hr class="mt-1 mb-3">
                                <label class="font-weight-bold" style="font-size:.85rem">
                                    <i class="fas fa-palette mr-1 text-primary"></i>
                                    Cor Principal da Marca
                                    <small class="text-muted font-weight-normal ml-1">— aplicada automaticamente no site público</small>
                                </label>
                                <div class="d-flex align-items-start flex-wrap mt-2" style="gap:16px">

                                    {{-- Color picker --}}
                                    <div>
                                        <div class="d-flex align-items-center" style="gap:8px">
                                            <input type="color" id="corPrimariaInput" name="cor_primaria"
                                                   value="{{ $settings['cor_primaria']->value ?? '#0066FF' }}"
                                                   style="width:48px;height:48px;border:none;padding:2px;border-radius:8px;cursor:pointer;background:transparent">
                                            <div>
                                                <div class="font-weight-bold" style="font-size:.82rem" id="corPrimariaHex">{{ $settings['cor_primaria']->value ?? '#0066FF' }}</div>
                                                <small class="text-muted">Cor selecionada</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Swatches extraídos da logo --}}
                                    <div id="colorSwatchesWrap" style="{{ empty($settings['logo_path']->value) ? 'display:none' : '' }}">
                                        <small class="d-block text-muted mb-1">Cores extraídas da logo — clique para selecionar:</small>
                                        <div id="colorSwatches" class="d-flex flex-wrap" style="gap:6px"></div>
                                    </div>

                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-magic mr-1"></i>
                                    Ao subir uma nova logo, as cores são extraídas automaticamente como sugestão. Você pode ajustar manualmente.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- ── Empresa ─────────────────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-empresa" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Razão Social</label>
                                    <input type="text" name="razao_social" class="form-control"
                                        value="{{ $settings['razao_social']->value ?? '' }}"
                                        placeholder="Ex: EMPRESA LTDA">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">CNPJ</label>
                                    <input type="text" name="cnpj" class="form-control"
                                        value="{{ $settings['cnpj']->value ?? '' }}"
                                        placeholder="00.000.000/0000-00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Cidade / Estado</label>
                                    <input type="text" name="cidade_estado" class="form-control"
                                        value="{{ $settings['cidade_estado']->value ?? '' }}"
                                        placeholder="Ex: Santa Maria de Jetibá – ES">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Telefone Comercial</label>
                                    <input type="text" name="telefone_comercial" class="form-control"
                                        value="{{ $settings['telefone_comercial']->value ?? '' }}"
                                        placeholder="(27) 3000-0000">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Endereço Completo</label>
                                    <input type="text" name="endereco_completo" class="form-control"
                                        value="{{ $settings['endereco_completo']->value ?? '' }}"
                                        placeholder="Ex: Rua das Flores, 123 – Centro – Santa Maria de Jetibá, ES">
                                    <small class="text-muted">Exibido no rodapé e na seção de contato do site.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Horário de Atendimento</label>
                                    <input type="text" name="horario_atendimento" class="form-control"
                                        value="{{ $settings['horario_atendimento']->value ?? '' }}"
                                        placeholder="Ex: Seg–Sex 8h–18h | Sáb 8h–12h">
                                    <small class="text-muted">Exibido no rodapé e na seção de contato do site.</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Descrição da Empresa <small class="text-muted font-weight-normal">(rodapé do site)</small></label>
                                    <textarea name="descricao_empresa" class="form-control" rows="2"
                                        placeholder="Ex: Sua loja de confiança para encontrar o carro seminovo ideal. Qualidade e transparência em cada negociação.">{{ $settings['descricao_empresa']->value ?? '' }}</textarea>
                                    <small class="text-muted">Texto curto exibido abaixo do logo no rodapé. Máx. 200 caracteres.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">URL de Incorporação do Google Maps <small class="text-muted font-weight-normal">(embed)</small></label>
                                    <input type="url" name="maps_embed_url" class="form-control"
                                        value="{{ $settings['maps_embed_url']->value ?? '' }}"
                                        placeholder="https://www.google.com/maps/embed?pb=...">
                                    <small class="text-muted">
                                        No Google Maps, clique em <strong>Compartilhar → Incorporar mapa</strong> e copie apenas a URL do atributo <code>src</code>.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Contato e Redes ──────────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-contato" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Número WhatsApp</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                        </div>
                                        <input type="text" name="whatsapp_number" class="form-control"
                                            value="{{ $settings['whatsapp_number']->value ?? '' }}"
                                            placeholder="5527999999999">
                                    </div>
                                    <small class="text-muted">Código do país + DDD + número, sem espaços ou traços.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Instagram</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                        </div>
                                        <input type="url" name="instagram_url" class="form-control"
                                            value="{{ $settings['instagram_url']->value ?? '' }}"
                                            placeholder="https://instagram.com/suaempresa">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Facebook</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                        </div>
                                        <input type="url" name="facebook_url" class="form-control"
                                            value="{{ $settings['facebook_url']->value ?? '' }}"
                                            placeholder="https://facebook.com/suaempresa">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Site Público ─────────────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-site" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Título da Página Inicial</label>
                                    <input type="text" name="site_titulo_home" class="form-control"
                                        value="{{ $settings['site_titulo_home']->value ?? '' }}"
                                        placeholder="Ex: Soavel Veículos | Seminovos">
                                    <small class="text-muted">Aparece na aba do navegador na home do site.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Descrição (meta description)</label>
                                    <input type="text" name="site_descricao_home" class="form-control"
                                        value="{{ $settings['site_descricao_home']->value ?? '' }}"
                                        placeholder="Ex: Carros Seminovos em Santa Maria de Jetibá, ES"
                                        maxlength="160">
                                    <small class="text-muted">Exibida nos resultados de busca (Google). Máx. 160 caracteres.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Título Principal (Hero)</label>
                                    <input type="text" name="hero_titulo" class="form-control"
                                        value="{{ $settings['hero_titulo']->value ?? '' }}"
                                        placeholder="Ex: Encontre o carro perfeito para você">
                                    <small class="text-muted">Título grande exibido no banner principal da home.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Estatística: Clientes Atendidos</label>
                                    <input type="text" name="stat_clientes" class="form-control"
                                        value="{{ $settings['stat_clientes']->value ?? '' }}"
                                        placeholder="Ex: 500+">
                                    <small class="text-muted">Exibido no hero da home.</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold" style="font-size:.85rem">Estatística: Anos no Mercado</label>
                                    <input type="text" name="stat_anos" class="form-control"
                                        value="{{ $settings['stat_anos']->value ?? '' }}"
                                        placeholder="Ex: 10">
                                    <small class="text-muted">Exibido no hero da home.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="font-weight-bold" style="font-size:.85rem">Layout / Tema do Site Público</label>
                                @php $currentLayout = $settings['site_layout']->value ?? 'clean-modern'; @endphp
                                <div class="row mt-2">

                                    {{-- Theme 1: Clean & Modern --}}
                                    <div class="col-md-4 mb-3">
                                        <label class="d-block theme-card-label cursor-pointer" style="cursor:pointer">
                                            <input type="radio" name="site_layout" value="clean-modern"
                                                   class="theme-radio d-none"
                                                   {{ $currentLayout === 'clean-modern' ? 'checked' : '' }}>
                                            <div class="card theme-option-card {{ $currentLayout === 'clean-modern' ? 'border-primary' : '' }}" style="border-width:2px;transition:border-color .2s">
                                                <div style="height:80px;background:linear-gradient(135deg,#EEF4FF,#F5F7FA);border-radius:4px 4px 0 0;display:flex;align-items:center;justify-content:center;gap:8px">
                                                    <div style="width:40px;height:24px;background:#fff;border-radius:6px;border:1px solid #E5E7EB;box-shadow:0 2px 6px rgba(0,0,0,.08)"></div>
                                                    <div style="display:flex;flex-direction:column;gap:4px">
                                                        <div style="width:50px;height:6px;background:#0066FF;border-radius:3px"></div>
                                                        <div style="width:36px;height:4px;background:#E5E7EB;border-radius:3px"></div>
                                                        <div style="width:44px;height:4px;background:#E5E7EB;border-radius:3px"></div>
                                                    </div>
                                                </div>
                                                <div class="card-body py-2 px-3">
                                                    <div class="font-weight-bold" style="font-size:.88rem">Clean &amp; Modern</div>
                                                    <small class="text-muted">Fundo branco, azul vibrante, elegante e profissional.</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Theme 2: Dark & Sport --}}
                                    <div class="col-md-4 mb-3">
                                        <label class="d-block theme-card-label" style="cursor:pointer">
                                            <input type="radio" name="site_layout" value="dark-sport"
                                                   class="theme-radio d-none"
                                                   {{ $currentLayout === 'dark-sport' ? 'checked' : '' }}>
                                            <div class="card theme-option-card {{ $currentLayout === 'dark-sport' ? 'border-primary' : '' }}" style="border-width:2px;transition:border-color .2s">
                                                <div style="height:80px;background:linear-gradient(135deg,#0D0D0D,#1A0A00);border-radius:4px 4px 0 0;display:flex;align-items:center;justify-content:center;gap:8px">
                                                    <div style="width:40px;height:24px;background:#1A1A1A;border-radius:3px;border:1px solid rgba(255,69,0,.3)"></div>
                                                    <div style="display:flex;flex-direction:column;gap:4px">
                                                        <div style="width:50px;height:6px;background:#FF4500;border-radius:2px"></div>
                                                        <div style="width:36px;height:4px;background:rgba(255,255,255,.15);border-radius:2px"></div>
                                                        <div style="width:44px;height:4px;background:rgba(255,255,255,.15);border-radius:2px"></div>
                                                    </div>
                                                </div>
                                                <div class="card-body py-2 px-3">
                                                    <div class="font-weight-bold" style="font-size:.88rem">Dark &amp; Sport</div>
                                                    <small class="text-muted">Fundo escuro, laranja vibrante, estilo esportivo agressivo.</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Theme 3: Premium --}}
                                    <div class="col-md-4 mb-3">
                                        <label class="d-block theme-card-label" style="cursor:pointer">
                                            <input type="radio" name="site_layout" value="premium"
                                                   class="theme-radio d-none"
                                                   {{ $currentLayout === 'premium' ? 'checked' : '' }}>
                                            <div class="card theme-option-card {{ $currentLayout === 'premium' ? 'border-primary' : '' }}" style="border-width:2px;transition:border-color .2s">
                                                <div style="height:80px;background:linear-gradient(135deg,#0A0A0A,#1A1408);border-radius:4px 4px 0 0;display:flex;align-items:center;justify-content:center;gap:8px">
                                                    <div style="width:40px;height:24px;background:#161616;border-radius:2px;border:1px solid rgba(201,168,76,.3)"></div>
                                                    <div style="display:flex;flex-direction:column;gap:4px">
                                                        <div style="width:50px;height:6px;background:linear-gradient(90deg,#C9A84C,#E5C974);border-radius:2px"></div>
                                                        <div style="width:36px;height:4px;background:rgba(245,240,232,.12);border-radius:2px"></div>
                                                        <div style="width:44px;height:4px;background:rgba(245,240,232,.12);border-radius:2px"></div>
                                                    </div>
                                                </div>
                                                <div class="card-body py-2 px-3">
                                                    <div class="font-weight-bold" style="font-size:.88rem">Premium</div>
                                                    <small class="text-muted">Fundo preto, dourado elegante, tipografia refinada.</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Theme 4: Automotive --}}
                                    <div class="col-md-4 mb-3">
                                        <label class="d-block theme-card-label" style="cursor:pointer">
                                            <input type="radio" name="site_layout" value="automotive"
                                                   class="theme-radio d-none"
                                                   {{ $currentLayout === 'automotive' ? 'checked' : '' }}>
                                            <div class="card theme-option-card {{ $currentLayout === 'automotive' ? 'border-primary' : '' }}" style="border-width:2px;transition:border-color .2s">
                                                <div style="height:80px;background:linear-gradient(135deg,#0F172A,#1E293B);border-radius:4px 4px 0 0;display:flex;align-items:center;justify-content:center;gap:8px">
                                                    <div style="width:40px;height:24px;background:#1E293B;border-radius:3px;border:1px solid rgba(220,38,38,.3)"></div>
                                                    <div style="display:flex;flex-direction:column;gap:4px">
                                                        <div style="width:50px;height:6px;background:#DC2626;border-radius:2px"></div>
                                                        <div style="width:36px;height:4px;background:rgba(248,250,252,.15);border-radius:2px"></div>
                                                        <div style="width:44px;height:4px;background:rgba(248,250,252,.15);border-radius:2px"></div>
                                                    </div>
                                                </div>
                                                <div class="card-body py-2 px-3">
                                                    <div class="font-weight-bold" style="font-size:.88rem">Automotive</div>
                                                    <small class="text-muted">Navy escuro, vermelho CTA, tipografia Syncopate — moderno e sofisticado.</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                </div>
                                <small class="text-muted">Escolha o estilo visual do site público. A alteração é imediata após salvar.</small>
                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-content --}}
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary" title="Salvar todas as configurações">
                    <i class="fas fa-save mr-1"></i>Salvar Configurações
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ── Modal Recorte de Logo (livre) ──────────────────────── --}}
<div class="modal fade" id="modalCropLogo" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-crop-alt mr-2"></i>Recortar Logo</h5>
                <button type="button" class="close" id="btnCropLogoCancel"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="background:#2b2b2b">
                <img id="cropperLogoImage" src="" alt="Logo" style="display:block;max-width:100%">
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center" style="gap:6px">
                    <div class="btn-group btn-group-sm mr-2">
                        <button type="button" class="btn btn-outline-secondary" id="btnLogoRotateL" title="Girar 90° esquerda"><i class="fas fa-undo"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnLogoRotateR" title="Girar 90° direita"><i class="fas fa-redo"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnLogoFlipH" title="Espelhar horizontal"><i class="fas fa-arrows-alt-h"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnLogoFlipV" title="Espelhar vertical"><i class="fas fa-arrows-alt-v"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnLogoReset" title="Restaurar"><i class="fas fa-sync-alt mr-1"></i>Resetar</button>
                    </div>
                    <div class="ml-2">
                        <small class="text-muted">Preview:</small><br>
                        <div id="cropperLogoPreview" style="width:160px;height:60px;overflow:hidden;border:1px solid #dee2e6;border-radius:4px;background:#fff"></div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm mr-1" id="btnCropLogoCancel2">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnCropLogoConfirm">
                        <i class="fas fa-check mr-1"></i>Aplicar Recorte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Recorte de Favicon (1:1) ─────────────────────── --}}
<div class="modal fade" id="modalCropFavicon" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-crop-alt mr-2"></i>Recortar Favicon <small class="text-muted">(quadrado)</small></h5>
                <button type="button" class="close" id="btnCropFaviconCancel"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="background:#2b2b2b">
                <img id="cropperFaviconImage" src="" alt="Favicon" style="display:block;max-width:100%">
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center" style="gap:6px">
                    <div class="btn-group btn-group-sm mr-2">
                        <button type="button" class="btn btn-outline-secondary" id="btnFaviconRotateL" title="Girar 90° esquerda"><i class="fas fa-undo"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnFaviconRotateR" title="Girar 90° direita"><i class="fas fa-redo"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnFaviconReset" title="Restaurar"><i class="fas fa-sync-alt mr-1"></i>Resetar</button>
                    </div>
                    <div class="ml-2">
                        <small class="text-muted">Preview 48px:</small><br>
                        <div id="cropperFaviconPreview" style="width:48px;height:48px;overflow:hidden;border:1px solid #dee2e6;border-radius:6px;background:#fff"></div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm mr-1" id="btnCropFaviconCancel2">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnCropFaviconConfirm">
                        <i class="fas fa-check mr-1"></i>Aplicar Recorte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.2/color-thief.umd.js"></script>
<script>
// ── Fábrica de croppers reutilizável ────────────────────────
function makeCropper(modalId, imgId, inputId, previewId, previewWrapId, previewLabelId, opts) {
    var cropper = null, sx = 1, sy = 1;

    document.getElementById(inputId).addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById(imgId).src = e.target.result;
            sx = 1; sy = 1;
            $('#' + modalId).modal('show');
        };
        reader.readAsDataURL(file);
    });

    $('#' + modalId).on('shown.bs.modal', function () {
        var img = document.getElementById(imgId);
        if (cropper) { cropper.destroy(); cropper = null; }
        cropper = new Cropper(img, Object.assign({
            viewMode: 1, dragMode: 'move', autoCropArea: 0.9,
            restore: false, guides: true, center: true, highlight: false,
            cropBoxMovable: true, cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview: '#' + previewId,
        }, opts));
    });

    $('#' + modalId).on('hidden.bs.modal', function () {
        if (cropper) { cropper.destroy(); cropper = null; }
    });

    return {
        rotate: function (deg) { cropper && cropper.rotate(deg); },
        flipH:  function () { if (!cropper) return; sx = -sx; cropper.scaleX(sx); },
        flipV:  function () { if (!cropper) return; sy = -sy; cropper.scaleY(sy); },
        reset:  function () { if (!cropper) return; sx = 1; sy = 1; cropper.reset(); },
        cancel: function () {
            var inp = document.getElementById(inputId);
            inp.value = ''; inp.nextElementSibling.textContent = inp.id === 'logo_path' ? 'Selecionar imagem...' : 'Selecionar ícone...';
            $('#' + modalId).modal('hide');
        },
        confirm: function (maxW, maxH, labelText, btnEl, previewImgId) {
            if (!cropper) return;
            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processando...';
            cropper.getCroppedCanvas({ maxWidth: maxW, maxHeight: maxH, imageSmoothingQuality: 'high' })
                .toBlob(function (blob) {
                    var inp = document.getElementById(inputId);
                    var name = inp.files[0] ? inp.files[0].name.replace(/\.[^.]+$/, '.png') : inputId + '.png';
                    var dt = new DataTransfer();
                    dt.items.add(new File([blob], name, { type: 'image/png' }));
                    inp.files = dt.files;
                    inp.nextElementSibling.textContent = name + ' (recortado)';
                    if (previewWrapId && previewImgId) {
                        document.getElementById(previewLabelId).textContent = labelText;
                        document.getElementById(previewImgId).src = URL.createObjectURL(blob);
                        document.getElementById(previewWrapId).style.display = '';
                    }
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fas fa-check mr-1"></i>Aplicar Recorte';
                    $('#' + modalId).modal('hide');
                }, 'image/png');
        }
    };
}

// ── Logo (livre) ────────────────────────────────────────────
var logoC = makeCropper('modalCropLogo', 'cropperLogoImage', 'logo_path', 'cropperLogoPreview', 'logoPreviewWrap', 'logoPreviewLabel', {});
document.getElementById('btnLogoRotateL').addEventListener('click',  function () { logoC.rotate(-90); });
document.getElementById('btnLogoRotateR').addEventListener('click',  function () { logoC.rotate(90); });
document.getElementById('btnLogoFlipH').addEventListener('click',    function () { logoC.flipH(); });
document.getElementById('btnLogoFlipV').addEventListener('click',    function () { logoC.flipV(); });
document.getElementById('btnLogoReset').addEventListener('click',    function () { logoC.reset(); });
['btnCropLogoCancel','btnCropLogoCancel2'].forEach(function (id) {
    document.getElementById(id).addEventListener('click', function () { logoC.cancel(); });
});
document.getElementById('btnCropLogoConfirm').addEventListener('click', function () {
    logoC.confirm(1200, 600, 'Nova logo (recortada) — será salva ao clicar em Salvar Configurações.', this, 'logoPreviewImg');
});

// ── Favicon (1:1) ───────────────────────────────────────────
var favC = makeCropper('modalCropFavicon', 'cropperFaviconImage', 'favicon_path', 'cropperFaviconPreview', 'faviconPreviewWrap', 'faviconPreviewLabel', { aspectRatio: 1 });
document.getElementById('btnFaviconRotateL').addEventListener('click', function () { favC.rotate(-90); });
document.getElementById('btnFaviconRotateR').addEventListener('click', function () { favC.rotate(90); });
document.getElementById('btnFaviconReset').addEventListener('click',   function () { favC.reset(); });
['btnCropFaviconCancel','btnCropFaviconCancel2'].forEach(function (id) {
    document.getElementById(id).addEventListener('click', function () { favC.cancel(); });
});
document.getElementById('btnCropFaviconConfirm').addEventListener('click', function () {
    favC.confirm(512, 512, 'Novo favicon (recortado) — será salvo ao clicar em Salvar Configurações.', this, 'faviconPreviewImg');
});

// ── Remover logo ────────────────────────────────────────────
document.getElementById('btnRemoveLogo').addEventListener('click', function () {
    document.getElementById('remove_logo').value = '1';
    document.getElementById('logo_path').value = '';
    document.getElementById('logo_path').nextElementSibling.textContent = 'Selecionar imagem...';
    document.getElementById('logoPreviewWrap').style.display = 'none';
});

// ── Remover favicon ─────────────────────────────────────────
document.getElementById('btnRemoveFavicon').addEventListener('click', function () {
    document.getElementById('remove_favicon').value = '1';
    document.getElementById('favicon_path').value = '';
    document.getElementById('favicon_path').nextElementSibling.textContent = 'Selecionar ícone...';
    document.getElementById('faviconPreviewImg').src = '{{ asset('img/default-favicon.svg') }}';
    document.getElementById('faviconPreviewLabel').textContent = 'Ícone padrão — envie um personalizado.';
    this.style.display = 'none';
});

// ── Seleção de tema ─────────────────────────────────────────
document.querySelectorAll('.theme-radio').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.theme-option-card').forEach(function (card) {
            card.classList.remove('border-primary');
        });
        if (this.checked) {
            this.closest('label').querySelector('.theme-option-card').classList.add('border-primary');
        }
    });
});

// ── Extração de cores da logo (Color Thief) ─────────────────
var colorThief = new ColorThief();

function rgbToHex(r, g, b) {
    return '#' + [r, g, b].map(function (v) { return v.toString(16).padStart(2, '0'); }).join('');
}

function renderSwatches(img) {
    try {
        var palette = colorThief.getPalette(img, 6);
        var swatchesEl = document.getElementById('colorSwatches');
        var wrapEl = document.getElementById('colorSwatchesWrap');
        swatchesEl.innerHTML = '';
        palette.forEach(function (rgb) {
            var hex = rgbToHex(rgb[0], rgb[1], rgb[2]);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.title = hex;
            btn.style.cssText = 'width:36px;height:36px;border-radius:6px;border:2px solid #dee2e6;cursor:pointer;background:' + hex + ';transition:transform .15s,border-color .15s';
            btn.addEventListener('mouseenter', function () { this.style.transform = 'scale(1.15)'; this.style.borderColor = '#495057'; });
            btn.addEventListener('mouseleave', function () { this.style.transform = ''; this.style.borderColor = '#dee2e6'; });
            btn.addEventListener('click', function () {
                document.getElementById('corPrimariaInput').value = hex;
                document.getElementById('corPrimariaHex').textContent = hex;
            });
            swatchesEl.appendChild(btn);
        });
        wrapEl.style.display = '';
    } catch (e) { /* imagem pode não estar pronta */ }
}

// Extrai da logo já cadastrada ao carregar a página
(function () {
    var existingLogo = document.getElementById('logoPreviewImg');
    if (existingLogo && existingLogo.src && existingLogo.naturalWidth > 0) {
        renderSwatches(existingLogo);
    } else if (existingLogo && existingLogo.src) {
        existingLogo.addEventListener('load', function () { renderSwatches(existingLogo); });
    }
})();

// Extrai após recorte/preview da nova logo
var _origLogoConfirm = document.getElementById('btnCropLogoConfirm').onclick;
document.getElementById('btnCropLogoConfirm').addEventListener('click', function () {
    setTimeout(function () {
        var img = document.getElementById('logoPreviewImg');
        if (img && img.src) {
            var tmp = new Image();
            tmp.crossOrigin = 'anonymous';
            tmp.onload = function () { renderSwatches(tmp); };
            tmp.src = img.src;
        }
    }, 400);
});

// Atualiza label ao mudar o color picker manualmente
document.getElementById('corPrimariaInput').addEventListener('input', function () {
    document.getElementById('corPrimariaHex').textContent = this.value;
});

// ── Máscaras ────────────────────────────────────────────────
$('[name="cnpj"]').mask('00.000.000/0000-00');
var phoneMask = function (val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; };
var phoneOpts = { onKeyPress: function (val, e, field, opts) { field.mask(phoneMask.apply({}, arguments), opts); } };
$('[name="telefone_comercial"]').mask(phoneMask, phoneOpts);
$('[name="whatsapp_number"]').on('input', function () { this.value = this.value.replace(/\D/g, ''); });
</script>
@endsection
