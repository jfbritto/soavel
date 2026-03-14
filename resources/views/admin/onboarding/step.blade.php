<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuração Inicial — Etapa {{ $step }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/imask@7.6.1/dist/imask.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; min-height: 100vh; }

        .onboarding-container { max-width: 720px; margin: 0 auto; padding: 30px 16px 60px; }

        /* Header */
        .ob-header { text-align: center; margin-bottom: 32px; }
        .ob-header h1 { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .ob-header p { color: #6c757d; font-size: .92rem; }

        /* Stepper */
        .ob-stepper { display: flex; justify-content: center; gap: 0; margin-bottom: 36px; position: relative; }
        .ob-step { display: flex; flex-direction: column; align-items: center; position: relative; flex: 1; max-width: 160px; }
        .ob-step-circle {
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; font-weight: 600; z-index: 2;
            border: 3px solid #dee2e6; background: #fff; color: #adb5bd;
            transition: all .3s;
        }
        .ob-step.active .ob-step-circle { border-color: #4361ee; background: #4361ee; color: #fff; }
        .ob-step.done .ob-step-circle   { border-color: #2ecc71; background: #2ecc71; color: #fff; }
        .ob-step-label { font-size: .72rem; margin-top: 6px; color: #adb5bd; font-weight: 500; text-align: center; }
        .ob-step.active .ob-step-label  { color: #4361ee; font-weight: 600; }
        .ob-step.done .ob-step-label    { color: #2ecc71; }

        .ob-step-line {
            position: absolute; top: 22px; left: calc(50% + 26px); right: calc(-50% + 26px);
            height: 3px; background: #dee2e6; z-index: 1;
        }
        .ob-step.done .ob-step-line { background: #2ecc71; }
        .ob-step:last-child .ob-step-line { display: none; }

        /* Card */
        .ob-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 32px; border: 1px solid #e9ecef;
        }
        .ob-card-title { font-size: 1.15rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .ob-card-subtitle { font-size: .85rem; color: #6c757d; margin-bottom: 24px; }

        /* Form */
        .ob-label { font-weight: 600; font-size: .85rem; color: #333; margin-bottom: 4px; }
        .ob-label .required { color: #e74c3c; }
        .ob-hint { font-size: .78rem; color: #999; margin-top: 2px; }
        .form-control:focus { border-color: #4361ee; box-shadow: 0 0 0 .2rem rgba(67,97,238,.15); }

        /* Upload area */
        .ob-upload {
            border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px;
            text-align: center; cursor: pointer; transition: all .2s;
            background: #fafbfc; position: relative;
        }
        .ob-upload:hover { border-color: #4361ee; background: #f0f4ff; }
        .ob-upload input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .ob-upload-icon { font-size: 1.5rem; color: #adb5bd; margin-bottom: 6px; }
        .ob-upload-text { font-size: .82rem; color: #6c757d; }
        .ob-upload-preview { max-height: 64px; margin-top: 8px; border-radius: 4px; }

        /* Actions */
        .ob-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 28px; }
        .btn-ob-prev { background: none; border: 1px solid #dee2e6; color: #666; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: .88rem; transition: all .2s; }
        .btn-ob-prev:hover { border-color: #adb5bd; color: #333; text-decoration: none; }
        .btn-ob-next { background: #4361ee; color: #fff; border: none; padding: 10px 32px; border-radius: 8px; font-weight: 600; font-size: .88rem; transition: all .2s; }
        .btn-ob-next:hover { background: #3451d1; }
        .btn-ob-skip { background: none; border: none; color: #adb5bd; font-size: .82rem; text-decoration: underline; cursor: pointer; }
        .btn-ob-skip:hover { color: #666; }

        /* Layout theme cards */
        .theme-option { cursor: pointer; border: 2px solid #e9ecef; border-radius: 10px; padding: 14px; text-align: center; transition: all .2s; }
        .theme-option:hover { border-color: #4361ee; }
        .theme-option.selected { border-color: #4361ee; background: #f0f4ff; }
        .theme-option input { display: none; }
        .theme-swatch { width: 100%; height: 40px; border-radius: 6px; margin-bottom: 8px; }
        .theme-name { font-size: .78rem; font-weight: 600; color: #333; }

        /* Color picker */
        .ob-color-wrap { display: flex; align-items: center; gap: 12px; }
        .ob-color-wrap input[type=color] { width: 48px; height: 40px; border: 2px solid #dee2e6; border-radius: 8px; cursor: pointer; padding: 2px; }
        .ob-color-wrap input[type=text] { max-width: 140px; }

        /* Success alert */
        .ob-alert { border-radius: 8px; font-size: .88rem; }

        /* Maps help tutorial */
        .maps-help { font-size: .82rem; }
        .maps-help-toggle {
            display: inline-flex; align-items: center; gap: 5px;
            color: #4361ee; text-decoration: none; font-weight: 500; font-size: .8rem;
        }
        .maps-help-toggle:hover { color: #3451d1; text-decoration: none; }
        .maps-help-toggle .chevron { font-size: .6rem; transition: transform .2s; }
        .maps-help-toggle .chevron.rotate { transform: rotate(180deg); }
        .maps-help-body {
            background: #f8f9fb; border: 1px solid #e9ecef; border-radius: 8px;
            padding: 16px; margin-top: 10px;
        }
        .maps-step {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 7px 0; font-size: .82rem; color: #444; line-height: 1.5;
        }
        .maps-step-num {
            flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%;
            background: #4361ee; color: #fff; font-size: .7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; margin-top: 1px;
        }
        .maps-code {
            display: block; background: #e9ecef; border-radius: 4px;
            padding: 6px 10px; margin-top: 6px; font-size: .75rem;
            word-break: break-all; color: #333;
        }
        .maps-code strong { color: #4361ee; }
        .maps-tip {
            background: #fff8e1; border: 1px solid #ffe082; border-radius: 6px;
            padding: 8px 12px; margin-top: 10px; font-size: .78rem; color: #6d5e00;
            display: flex; align-items: center; gap: 8px;
        }
        .maps-tip i { color: #f9a825; }

        /* AI Button */
        .btn-ai {
            display: inline-flex; align-items: center; gap: 6px;
            background: linear-gradient(135deg, #7c3aed, #4361ee); color: #fff;
            border: none; padding: 8px 18px; border-radius: 8px;
            font-size: .82rem; font-weight: 600; cursor: pointer;
            transition: all .25s; box-shadow: 0 2px 8px rgba(67,97,238,.25);
        }
        .btn-ai:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(67,97,238,.35); }
        .btn-ai:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .btn-ai .spinner { display: none; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; }
        .btn-ai.loading .spinner { display: inline-block; }
        .btn-ai.loading .btn-ai-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .ai-banner {
            background: linear-gradient(135deg, #f3f0ff, #eef2ff); border: 1px solid #c7d2fe;
            border-radius: 10px; padding: 16px 20px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
        }
        .ai-banner-icon { font-size: 1.6rem; color: #7c3aed; }
        .ai-banner-text { flex: 1; min-width: 200px; }
        .ai-banner-text strong { color: #4338ca; font-size: .88rem; }
        .ai-banner-text p { margin: 2px 0 0; font-size: .78rem; color: #6366f1; }

        .ai-field-indicator {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: .7rem; color: #7c3aed; font-weight: 600;
            background: #f3f0ff; padding: 2px 8px; border-radius: 4px;
            margin-left: 8px; vertical-align: middle;
        }
        .ai-field-indicator i { font-size: .6rem; }

        .ai-toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            background: #fff; border-radius: 10px; padding: 14px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,.12); border-left: 4px solid #2ecc71;
            font-size: .85rem; max-width: 340px;
            transform: translateY(100px); opacity: 0; transition: all .3s;
        }
        .ai-toast.show { transform: translateY(0); opacity: 1; }
        .ai-toast.error { border-left-color: #e74c3c; }

        @media (max-width: 576px) {
            .ob-card { padding: 20px 16px; }
            .ob-step-label { font-size: .65rem; }
            .ob-step-circle { width: 36px; height: 36px; font-size: .85rem; }
            .ob-step-line { top: 18px; left: calc(50% + 22px); right: calc(-50% + 22px); }
        }
    </style>
</head>
<body>

<div class="onboarding-container">

    {{-- Header --}}
    <div class="ob-header">
        @php $logoPath = \App\Models\Setting::get('logo_path'); @endphp
        @if($logoPath)
            <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" style="max-height:48px;margin-bottom:12px">
        @endif
        <h1>Configuracao Inicial</h1>
        <p>Preencha os dados da sua loja para deixar o site pronto para seus clientes.</p>
    </div>

    {{-- Stepper --}}
    <div class="ob-stepper">
        @foreach($allSteps as $num => $cfg)
            <div class="ob-step {{ $num < $step ? 'done' : '' }} {{ $num === $step ? 'active' : '' }}">
                <div class="ob-step-circle">
                    @if($num < $step)
                        <i class="fas fa-check"></i>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="ob-step-label">{{ $cfg['title'] }}</span>
                @if($num < $totalSteps)
                    <div class="ob-step-line"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success ob-alert mb-3">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger ob-alert mb-3">
            <i class="fas fa-exclamation-circle mr-1"></i>
            @foreach($errors->all() as $e) {{ $e }}<br>@endforeach
        </div>
    @endif

    {{-- Card --}}
    <form action="{{ route('admin.onboarding.save', $step) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="ob-card">

            <div class="ob-card-title"><i class="{{ $stepConfig['icon'] }} mr-2" style="color:#4361ee"></i>{{ $stepConfig['title'] }}</div>
            <p class="ob-card-subtitle">
                @switch($step)
                    @case(1) Defina o nome e a marca visual da sua loja. @break
                    @case(2) Informe os dados da empresa para exibir no site e nas notas. @break
                    @case(3) Configure os canais de atendimento e redes sociais. @break
                    @case(4) Personalize a aparencia do site publico. @break
                @endswitch
            </p>

            {{-- ═══════════ ETAPA 1: Identidade ═══════════ --}}
            @if($step === 1)
                <div class="form-group">
                    <label class="ob-label">Nome do Sistema / Loja <span class="required">*</span></label>
                    <input type="text" name="nome_sistema" id="nome_sistema" class="form-control"
                           value="{{ old('nome_sistema', $settings['nome_sistema']->value ?? '') }}"
                           placeholder="Ex: Auto Premium Veiculos" required>
                    <p class="ob-hint">Aparece no cabecalho, rodape e titulo das paginas.</p>
                </div>
                <div class="form-group">
                    <label class="ob-label">Slogan <span class="ai-field-indicator"><i class="fas fa-wand-magic-sparkles"></i> IA</span></label>
                    <input type="text" name="slogan" id="ai_slogan" class="form-control"
                           value="{{ old('slogan', $settings['slogan']->value ?? '') }}"
                           placeholder="Ex: Seu proximo carro esta aqui">
                    <p class="ob-hint">Texto exibido abaixo do titulo no banner principal.</p>
                </div>

                {{-- AI Banner --}}
                <div class="ai-banner" id="aiBannerStep1">
                    <div class="ai-banner-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                    <div class="ai-banner-text">
                        <strong>Assistente IA</strong>
                        <p>Preencha o nome da loja e clique para gerar automaticamente o slogan e textos das proximas etapas.</p>
                    </div>
                    <button type="button" class="btn-ai" id="btnAiStep1" onclick="generateWithAI()">
                        <span class="spinner"></span>
                        <span class="btn-ai-text"><i class="fas fa-wand-magic-sparkles"></i> Gerar com IA</span>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Logo da Loja</label>
                            <div class="ob-upload" id="logoUpload">
                                <div class="ob-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="ob-upload-text">Clique ou arraste a logo aqui</div>
                                <small class="text-muted">JPG, PNG ou WebP — max 2 MB</small>
                                @if($settings['logo_path']->value ?? null)
                                    <br><img src="{{ asset('storage/' . $settings['logo_path']->value) }}" class="ob-upload-preview" id="logoPreview">
                                @else
                                    <img src="" class="ob-upload-preview d-none" id="logoPreview">
                                @endif
                                <input type="file" name="logo_path" accept="image/*" onchange="previewFile(this, 'logoPreview')">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Favicon</label>
                            <div class="ob-upload" id="faviconUpload">
                                <div class="ob-upload-icon"><i class="fas fa-image"></i></div>
                                <div class="ob-upload-text">Clique ou arraste o favicon</div>
                                <small class="text-muted">Icone do navegador — max 512 KB</small>
                                @if($settings['favicon_path']->value ?? null)
                                    <br><img src="{{ asset('storage/' . $settings['favicon_path']->value) }}" class="ob-upload-preview" id="faviconPreview">
                                @else
                                    <img src="" class="ob-upload-preview d-none" id="faviconPreview">
                                @endif
                                <input type="file" name="favicon_path" accept="image/*" onchange="previewFile(this, 'faviconPreview')">
                            </div>
                        </div>
                    </div>
                </div>

            {{-- ═══════════ ETAPA 2: Empresa ═══════════ --}}
            @elseif($step === 2)
                {{-- AI Banner --}}
                <div class="ai-banner" id="aiBannerStep2">
                    <div class="ai-banner-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                    <div class="ai-banner-text">
                        <strong>Assistente IA</strong>
                        <p>Preencha a cidade e clique para gerar a descricao da empresa e horario de atendimento.</p>
                    </div>
                    <button type="button" class="btn-ai" id="btnAiStep2" onclick="generateWithAI()">
                        <span class="spinner"></span>
                        <span class="btn-ai-text"><i class="fas fa-wand-magic-sparkles"></i> Gerar com IA</span>
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Razao Social</label>
                            <input type="text" name="razao_social" class="form-control"
                                   value="{{ old('razao_social', $settings['razao_social']->value ?? '') }}"
                                   placeholder="Ex: Auto Premium Ltda">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">CNPJ</label>
                            <input type="text" name="cnpj" id="mask_cnpj" class="form-control"
                                   value="{{ old('cnpj', $settings['cnpj']->value ?? '') }}"
                                   placeholder="00.000.000/0000-00" inputmode="numeric">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Cidade / Estado</label>
                            <input type="text" name="cidade_estado" class="form-control"
                                   value="{{ old('cidade_estado', $settings['cidade_estado']->value ?? '') }}"
                                   placeholder="Ex: Vitoria - ES">
                            <p class="ob-hint">Exibido no rodape e usado para SEO local.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Telefone Comercial</label>
                            <input type="text" name="telefone_comercial" id="mask_telefone" class="form-control"
                                   value="{{ old('telefone_comercial', $settings['telefone_comercial']->value ?? '') }}"
                                   placeholder="(27) 99999-9999" inputmode="tel">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="ob-label">Endereco Completo</label>
                    <input type="text" name="endereco_completo" class="form-control"
                           value="{{ old('endereco_completo', $settings['endereco_completo']->value ?? '') }}"
                           placeholder="Ex: Av. Fernando Ferrari, 1000 - Goiabeiras, Vitoria - ES">
                </div>
                <div class="form-group">
                    <label class="ob-label">Horario de Atendimento <span class="ai-field-indicator"><i class="fas fa-wand-magic-sparkles"></i> IA</span></label>
                    <input type="text" name="horario_atendimento" id="ai_horario_atendimento" class="form-control"
                           value="{{ old('horario_atendimento', $settings['horario_atendimento']->value ?? '') }}"
                           placeholder="Ex: Seg-Sex 8h-18h | Sab 8h-12h">
                </div>
                <div class="form-group">
                    <label class="ob-label">Descricao da Empresa <span class="ai-field-indicator"><i class="fas fa-wand-magic-sparkles"></i> IA</span></label>
                    <textarea name="descricao_empresa" id="ai_descricao_empresa" class="form-control" rows="3"
                              placeholder="Breve descricao da loja para o rodape e SEO (max 200 caracteres)"
                              maxlength="200">{{ old('descricao_empresa', $settings['descricao_empresa']->value ?? '') }}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="ob-label">URL do Google Maps (embed)</label>
                    <input type="url" name="maps_embed_url" id="maps_embed_url" class="form-control"
                           value="{{ old('maps_embed_url', $settings['maps_embed_url']->value ?? '') }}"
                           placeholder="https://www.google.com/maps/embed?pb=...">

                    {{-- Tutorial passo a passo --}}
                    <div class="maps-help mt-2">
                        <a href="#" class="maps-help-toggle" onclick="event.preventDefault(); document.getElementById('mapsHelpContent').classList.toggle('d-none'); this.querySelector('.chevron').classList.toggle('rotate');">
                            <i class="fas fa-circle-question"></i> Como pegar o link do Google Maps?
                            <i class="fas fa-chevron-down chevron"></i>
                        </a>
                        <div id="mapsHelpContent" class="maps-help-body d-none">
                            <div class="maps-step">
                                <span class="maps-step-num">1</span>
                                <div>
                                    Abra o <a href="https://www.google.com/maps" target="_blank" rel="noopener"><strong>Google Maps</strong></a> e pesquise o endereco da sua loja.
                                </div>
                            </div>
                            <div class="maps-step">
                                <span class="maps-step-num">2</span>
                                <div>
                                    Clique no botao <strong>Compartilhar</strong> <i class="fas fa-share-alt" style="font-size:.8rem"></i> (ou no menu <i class="fas fa-ellipsis-v" style="font-size:.8rem"></i> do local).
                                </div>
                            </div>
                            <div class="maps-step">
                                <span class="maps-step-num">3</span>
                                <div>
                                    Na janela que abrir, clique na aba <strong>"Incorporar um mapa"</strong>.
                                </div>
                            </div>
                            <div class="maps-step">
                                <span class="maps-step-num">4</span>
                                <div>
                                    Clique em <strong>"COPIAR HTML"</strong>. Voce vai copiar algo assim:
                                    <code class="maps-code">&lt;iframe src="<strong>https://www.google.com/maps/embed?pb=...</strong>" ...&gt;&lt;/iframe&gt;</code>
                                </div>
                            </div>
                            <div class="maps-step">
                                <span class="maps-step-num">5</span>
                                <div>
                                    Cole aqui no campo acima <strong>apenas a URL</strong> que esta dentro do <code>src="..."</code>.
                                    <br><small class="text-muted">Comeca com <code>https://www.google.com/maps/embed?pb=</code></small>
                                </div>
                            </div>
                            <div class="maps-tip">
                                <i class="fas fa-lightbulb"></i>
                                <strong>Dica:</strong> Se voce colou o HTML inteiro por engano, o sistema vai extrair a URL automaticamente.
                            </div>
                        </div>
                    </div>
                </div>

            {{-- ═══════════ ETAPA 3: Contato ═══════════ --}}
            @elseif($step === 3)
                <div class="form-group">
                    <label class="ob-label">Numero do WhatsApp <span class="required">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-whatsapp" style="color:#25d366"></i></span>
                        </div>
                        <input type="text" id="mask_whatsapp" class="form-control"
                               value="{{ old('whatsapp_number', $settings['whatsapp_number']->value ?? '') }}"
                               placeholder="+55 (27) 99999-9999" inputmode="tel" required>
                        <input type="hidden" name="whatsapp_number" id="whatsapp_raw">
                    </div>
                    <p class="ob-hint">Digite com DDD. Ex: +55 (27) 99999-9999</p>
                </div>
                <div class="form-group">
                    <label class="ob-label">Instagram</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-instagram" style="color:#e1306c"></i></span>
                        </div>
                        <input type="url" name="instagram_url" class="form-control"
                               value="{{ old('instagram_url', $settings['instagram_url']->value ?? '') }}"
                               placeholder="https://instagram.com/sualoja">
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="ob-label">Facebook</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-facebook-f" style="color:#1877f2"></i></span>
                        </div>
                        <input type="url" name="facebook_url" class="form-control"
                               value="{{ old('facebook_url', $settings['facebook_url']->value ?? '') }}"
                               placeholder="https://facebook.com/sualoja">
                    </div>
                </div>

            {{-- ═══════════ ETAPA 4: Site ═══════════ --}}
            @elseif($step === 4)
                {{-- AI Banner --}}
                <div class="ai-banner" id="aiBannerStep4">
                    <div class="ai-banner-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                    <div class="ai-banner-text">
                        <strong>Assistente IA</strong>
                        <p>Gere automaticamente titulo SEO, descricao e texto do banner com base nos dados da loja.</p>
                    </div>
                    <button type="button" class="btn-ai" id="btnAiStep4" onclick="generateWithAI()">
                        <span class="spinner"></span>
                        <span class="btn-ai-text"><i class="fas fa-wand-magic-sparkles"></i> Gerar com IA</span>
                    </button>
                </div>

                <div class="form-group">
                    <label class="ob-label">Titulo da Pagina Inicial (SEO) <span class="ai-field-indicator"><i class="fas fa-wand-magic-sparkles"></i> IA</span></label>
                    <input type="text" name="site_titulo_home" id="ai_site_titulo_home" class="form-control"
                           value="{{ old('site_titulo_home', $settings['site_titulo_home']->value ?? '') }}"
                           placeholder="Ex: Seminovos em Vitoria | Auto Premium">
                    <p class="ob-hint">Aparece na aba do navegador e nos resultados do Google.</p>
                </div>
                <div class="form-group">
                    <label class="ob-label">Descricao (meta description) <span class="ai-field-indicator"><i class="fas fa-wand-magic-sparkles"></i> IA</span></label>
                    <textarea name="site_descricao_home" id="ai_site_descricao_home" class="form-control" rows="2" maxlength="160"
                              placeholder="Texto que aparece nos resultados de busca do Google (max 160 caracteres)">{{ old('site_descricao_home', $settings['site_descricao_home']->value ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="ob-label">Titulo Principal (Banner Hero) <span class="ai-field-indicator"><i class="fas fa-wand-magic-sparkles"></i> IA</span></label>
                    <input type="text" name="hero_titulo" id="ai_hero_titulo" class="form-control"
                           value="{{ old('hero_titulo', $settings['hero_titulo']->value ?? '') }}"
                           placeholder="Ex: Encontre o Carro dos Seus Sonhos">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Estatistica: Clientes Atendidos</label>
                            <input type="text" name="stat_clientes" class="form-control"
                                   value="{{ old('stat_clientes', $settings['stat_clientes']->value ?? '') }}"
                                   placeholder="Ex: 500+">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="ob-label">Estatistica: Anos no Mercado</label>
                            <input type="text" name="stat_anos" class="form-control"
                                   value="{{ old('stat_anos', $settings['stat_anos']->value ?? '') }}"
                                   placeholder="Ex: 10">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="ob-label">Cor da Marca</label>
                    <div class="ob-color-wrap">
                        <input type="color" id="corPrimariaColor"
                               value="{{ old('cor_primaria', $settings['cor_primaria']->value ?? '#1e3a5f') }}"
                               onchange="document.getElementById('corPrimariaText').value = this.value">
                        <input type="text" name="cor_primaria" id="corPrimariaText" class="form-control"
                               value="{{ old('cor_primaria', $settings['cor_primaria']->value ?? '') }}"
                               placeholder="#1e3a5f"
                               onchange="document.getElementById('corPrimariaColor').value = this.value || '#1e3a5f'">
                    </div>
                    <p class="ob-hint">Cor principal usada em botoes, links e destaques do site.</p>
                </div>

                <div class="form-group mb-0">
                    <label class="ob-label">Tema do Site</label>
                    @php $currentLayout = old('site_layout', $settings['site_layout']->value ?? 'clean-modern'); @endphp
                    <div class="row mt-2">
                        <div class="col-6 col-md-3 mb-2">
                            <label class="theme-option d-block {{ $currentLayout === 'clean-modern' ? 'selected' : '' }}" onclick="selectTheme(this)">
                                <input type="radio" name="site_layout" value="clean-modern" {{ $currentLayout === 'clean-modern' ? 'checked' : '' }}>
                                <div class="theme-swatch" style="background: linear-gradient(135deg, #fff 60%, #4361ee 100%)"></div>
                                <div class="theme-name">Moderno</div>
                            </label>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="theme-option d-block {{ $currentLayout === 'dark-sport' ? 'selected' : '' }}" onclick="selectTheme(this)">
                                <input type="radio" name="site_layout" value="dark-sport" {{ $currentLayout === 'dark-sport' ? 'checked' : '' }}>
                                <div class="theme-swatch" style="background: linear-gradient(135deg, #1a1a2e 60%, #ff6b35 100%)"></div>
                                <div class="theme-name">Esportivo</div>
                            </label>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="theme-option d-block {{ $currentLayout === 'premium' ? 'selected' : '' }}" onclick="selectTheme(this)">
                                <input type="radio" name="site_layout" value="premium" {{ $currentLayout === 'premium' ? 'checked' : '' }}>
                                <div class="theme-swatch" style="background: linear-gradient(135deg, #0d0d0d 60%, #c9a84c 100%)"></div>
                                <div class="theme-name">Premium</div>
                            </label>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="theme-option d-block {{ $currentLayout === 'automotive' ? 'selected' : '' }}" onclick="selectTheme(this)">
                                <input type="radio" name="site_layout" value="automotive" {{ $currentLayout === 'automotive' ? 'checked' : '' }}>
                                <div class="theme-swatch" style="background: linear-gradient(135deg, #0f1c3f 60%, #e63946 100%)"></div>
                                <div class="theme-name">Automotivo</div>
                            </label>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="theme-option d-block {{ $currentLayout === 'elegance' ? 'selected' : '' }}" onclick="selectTheme(this)">
                                <input type="radio" name="site_layout" value="elegance" {{ $currentLayout === 'elegance' ? 'checked' : '' }}>
                                <div class="theme-swatch" style="background: linear-gradient(135deg, #f5f1eb 60%, #2D5A3D 100%)"></div>
                                <div class="theme-name">Elegance</div>
                            </label>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="theme-option d-block {{ $currentLayout === 'showcase' ? 'selected' : '' }}" onclick="selectTheme(this)">
                                <input type="radio" name="site_layout" value="showcase" {{ $currentLayout === 'showcase' ? 'checked' : '' }}>
                                <div class="theme-swatch" style="background: linear-gradient(135deg, #1a1a2e 60%, #FF6B2C 100%)"></div>
                                <div class="theme-name">Showcase</div>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Actions --}}
        <div class="ob-actions">
            <div>
                @if($step > 1)
                    <a href="{{ route('admin.onboarding.step', $step - 1) }}" class="btn-ob-prev">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                @else
                    <span></span>
                @endif
            </div>
            <div class="d-flex align-items-center" style="gap:12px">
                @if($step > 1 && $step < $totalSteps)
                    <a href="{{ route('admin.onboarding.step', $step + 1) }}" class="btn-ob-skip">Pular etapa</a>
                @endif
                <button type="submit" class="btn-ob-next">
                    @if($step === $totalSteps)
                        Finalizar <i class="fas fa-check ml-1"></i>
                    @else
                        Continuar <i class="fas fa-arrow-right ml-1"></i>
                    @endif
                </button>
            </div>
        </div>
    </form>

</div>

<script>
function previewFile(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function selectTheme(el) {
    document.querySelectorAll('.theme-option').forEach(function(t) { t.classList.remove('selected'); });
    el.classList.add('selected');
}

// ── Google Maps: extrai URL se colar HTML do iframe ─────────────
(function() {
    var mapsField = document.getElementById('maps_embed_url');
    if (!mapsField) return;

    mapsField.addEventListener('paste', function(e) {
        setTimeout(function() {
            var val = mapsField.value.trim();
            // Se colou o HTML inteiro do iframe, extrai o src
            if (val.indexOf('<iframe') !== -1 || val.indexOf('src=') !== -1) {
                var match = val.match(/src=["']([^"']+)["']/);
                if (match && match[1]) {
                    mapsField.value = match[1];
                    mapsField.style.transition = 'background .4s';
                    mapsField.style.background = '#f0fdf4';
                    setTimeout(function() { mapsField.style.background = ''; }, 1500);
                }
            }
        }, 50);
    });
})();

// ── Input Masks (IMask) ──────────────────────────────────────────
(function() {
    // CNPJ: 00.000.000/0000-00
    var cnpjEl = document.getElementById('mask_cnpj');
    if (cnpjEl) {
        IMask(cnpjEl, { mask: '00.000.000/0000-00' });
    }

    // Telefone: (00) 0000-0000 ou (00) 00000-0000
    var telEl = document.getElementById('mask_telefone');
    if (telEl) {
        IMask(telEl, {
            mask: [
                { mask: '(00) 0000-0000' },
                { mask: '(00) 00000-0000' }
            ],
            dispatch: function(appended, dynamicMasked) {
                var val = (dynamicMasked.value + appended).replace(/\D/g, '');
                return val.length > 10
                    ? dynamicMasked.compiledMasks[1]
                    : dynamicMasked.compiledMasks[0];
            }
        });
    }

    // WhatsApp: +00 (00) 00000-0000 — exibe formatado, envia dígitos puros
    var wpEl  = document.getElementById('mask_whatsapp');
    var wpRaw = document.getElementById('whatsapp_raw');
    if (wpEl && wpRaw) {
        // Se o valor já veio como dígitos puros (ex: 5527999999999), formata para exibição
        var initial = wpEl.value.replace(/\D/g, '');

        var wpMask = IMask(wpEl, {
            mask: '+00 (00) 00000-0000'
        });

        // Aplica valor inicial formatado
        if (initial) {
            wpMask.unmaskedValue = initial;
        }

        // Sync ao digitar
        wpEl.addEventListener('input', function() {
            wpRaw.value = wpMask.unmaskedValue;
        });
        // Sync inicial
        wpRaw.value = wpMask.unmaskedValue;

        // Garante sync antes do submit
        var form = wpEl.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                wpRaw.value = wpMask.unmaskedValue;
            });
        }
    }
})();

// ── AI Generation ────────────────────────────────────────────────
var aiSuggestions = null; // Cache das sugestões entre etapas

function showToast(msg, isError) {
    var existing = document.querySelector('.ai-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.className = 'ai-toast' + (isError ? ' error' : '');
    toast.innerHTML = '<i class="fas fa-' + (isError ? 'exclamation-circle' : 'check-circle') + '" style="color:' + (isError ? '#e74c3c' : '#2ecc71') + ';margin-right:6px"></i>' + msg;
    document.body.appendChild(toast);
    setTimeout(function() { toast.classList.add('show'); }, 50);
    setTimeout(function() { toast.classList.remove('show'); setTimeout(function() { toast.remove(); }, 300); }, 4000);
}

function setFieldIfEmpty(id, value) {
    var el = document.getElementById(id);
    if (el && value) {
        if (!el.value.trim()) {
            el.value = value;
            el.style.transition = 'background .4s';
            el.style.background = '#f0fdf4';
            setTimeout(function() { el.style.background = ''; }, 1500);
        }
    }
}

function setFieldAlways(id, value) {
    var el = document.getElementById(id);
    if (el && value) {
        el.value = value;
        el.style.transition = 'background .4s';
        el.style.background = '#f0fdf4';
        setTimeout(function() { el.style.background = ''; }, 1500);
    }
}

function applySuggestions(data, forceOverwrite) {
    var setter = forceOverwrite ? setFieldAlways : setFieldIfEmpty;
    var mapping = {
        'slogan': 'ai_slogan',
        'descricao_empresa': 'ai_descricao_empresa',
        'horario_sugerido': 'ai_horario_atendimento',
        'hero_titulo': 'ai_hero_titulo',
        'site_titulo_home': 'ai_site_titulo_home',
        'site_descricao_home': 'ai_site_descricao_home',
    };
    var count = 0;
    for (var key in mapping) {
        if (data[key]) {
            var el = document.getElementById(mapping[key]);
            if (el) { setter(mapping[key], data[key]); count++; }
        }
    }
    return count;
}

function generateWithAI() {
    var nomeLoja = '';
    var cidade = '';

    // Pega nome da loja — do campo atual ou dos settings salvos
    var nomeField = document.getElementById('nome_sistema');
    if (nomeField) {
        nomeLoja = nomeField.value.trim();
    }
    if (!nomeLoja) {
        nomeLoja = @json($settings['nome_sistema']->value ?? '');
    }

    // Pega cidade — do campo atual ou dos settings salvos
    var cidadeField = document.querySelector('[name="cidade_estado"]');
    if (cidadeField) {
        cidade = cidadeField.value.trim();
    }
    if (!cidade) {
        cidade = @json($settings['cidade_estado']->value ?? '');
    }

    if (!nomeLoja) {
        showToast('Preencha o nome da loja antes de gerar com IA.', true);
        if (nomeField) nomeField.focus();
        return;
    }

    // Encontra o botão ativo
    var btns = document.querySelectorAll('.btn-ai');
    btns.forEach(function(b) { b.classList.add('loading'); b.disabled = true; });

    fetch('{{ route("admin.onboarding.ai") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            nome_loja: nomeLoja,
            cidade: cidade,
            segmento: 'seminovos e usados',
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btns.forEach(function(b) { b.classList.remove('loading'); b.disabled = false; });

        if (data.error) {
            showToast(data.error, true);
            return;
        }

        if (data.suggestions) {
            aiSuggestions = data.suggestions;
            var count = applySuggestions(data.suggestions, false);

            if (count > 0) {
                showToast(count + ' campo(s) preenchido(s) pela IA!', false);
            } else {
                showToast('Todos os campos ja estavam preenchidos. Clique novamente para sobrescrever.', false);
                // Na segunda vez, sobrescreve
                btns.forEach(function(b) {
                    b.onclick = function() { overwriteWithAI(); };
                });
            }
        }
    })
    .catch(function(err) {
        btns.forEach(function(b) { b.classList.remove('loading'); b.disabled = false; });
        showToast('Erro de conexao. Tente novamente.', true);
    });
}

function overwriteWithAI() {
    if (aiSuggestions) {
        var count = applySuggestions(aiSuggestions, true);
        showToast(count + ' campo(s) atualizado(s) pela IA!', false);
    }
    // Restaura comportamento normal
    document.querySelectorAll('.btn-ai').forEach(function(b) {
        b.onclick = function() { generateWithAI(); };
    });
}
</script>

{{-- Toast container --}}
<div id="aiToastContainer"></div>

</body>
</html>
