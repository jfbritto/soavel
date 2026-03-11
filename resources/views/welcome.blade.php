@extends('layouts.site')

@section('meta_title', \App\Models\Setting::get('site_titulo_home', 'Soavel Veículos | Seminovos em Santa Maria de Jetibá - ES'))
@section('meta_description', \App\Models\Setting::get('site_descricao_home', 'Encontre carros seminovos de qualidade em Santa Maria de Jetibá-ES. Confira nosso estoque e encontre o veículo ideal para você.'))

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AutoDealer",
  "name": "{{ \App\Models\Setting::get('nome_sistema', 'Soavel Veículos') }}",
  "image": "{{ asset('img/logo/soavel-fundo.png') }}",
  "@id": "{{ url('/') }}",
  "url": "{{ url('/') }}",
  "telephone": "+{{ \App\Models\Setting::get('whatsapp_number', '5527998490472') }}",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Santa Maria de Jetibá",
    "addressRegion": "ES",
    "addressCountry": "BR"
  },
  "openingHours": "Mo-Sa 08:00-18:00"
}
</script>
@endsection

@section('content')

@php
    $whatsapp         = \App\Models\Setting::get('whatsapp_number', '5527998490472');
    $slogan           = \App\Models\Setting::get('slogan', 'Seu próximo carro está aqui');
    $nomeSistema      = \App\Models\Setting::get('nome_sistema', 'Soavel Veículos');
    $telefone         = \App\Models\Setting::get('telefone_comercial', '(27) 99849-0472');
    $cidadeEstado     = \App\Models\Setting::get('cidade_estado', 'Santa Maria de Jetibá – ES');
    $enderecoCompleto = \App\Models\Setting::get('endereco_completo', '');
    $horarioAten      = \App\Models\Setting::get('horario_atendimento', 'Seg–Sex 8h–18h | Sáb 8h–12h');
    $heroTitulo       = \App\Models\Setting::get('hero_titulo', '');
    $statClientes     = \App\Models\Setting::get('stat_clientes', '500+');
    $statAnos         = \App\Models\Setting::get('stat_anos', '10');
    $mapsEmbedUrl     = \App\Models\Setting::get('maps_embed_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4417.9692150682395!2d-40.74408482403657!3d-20.001308640534603!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xb77d27c247513d%3A0xc155ed404cf6cdcd!2sSoavel%20Ve%C3%ADculos!5e1!3m2!1spt-BR!2sbr!4v1742262481187!5m2!1spt-BR!2sbr');
@endphp

{{-- ═══════════════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════════════ --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">

            {{-- Left: Text + Search --}}
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-eyebrow">ESTOQUE ATUALIZADO</div>

                <h1 class="hero-title">
                    @if($heroTitulo)
                        {!! nl2br(e($heroTitulo)) !!}
                    @else
                        Encontre o carro<br>
                        perfeito para <span class="accent">você</span>
                    @endif
                </h1>

                <p class="hero-subtitle">{{ $slogan }}</p>

                {{-- Search Form --}}
                <div class="hero-search">
                    <form action="{{ route('site.vehicles.index') }}" method="GET">
                        <div class="row no-gutters" style="gap:0">
                            <div class="col-12 col-sm-4 pr-sm-1 mb-2 mb-sm-0">
                                <select name="marca" class="form-control">
                                    <option value="">Todas as marcas</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca }}">{{ $marca }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-4 px-sm-1 mb-2 mb-sm-0">
                                <select name="categoria" class="form-control">
                                    <option value="">Categoria</option>
                                    <option value="hatch">Hatch</option>
                                    <option value="sedan">Sedan</option>
                                    <option value="suv">SUV</option>
                                    <option value="pickup">Pickup</option>
                                    <option value="van">Van</option>
                                    <option value="esportivo">Esportivo</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-4 pl-sm-1 mb-3 mb-sm-0">
                                <input type="number" name="preco_max" class="form-control" placeholder="Preço máx. (R$)" min="0" step="1000">
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn-search w-100">
                                <i class="fas fa-search mr-2"></i>Buscar Veículo
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Stats --}}
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-value">{{ $totalVehiculos }}+</span>
                        <span class="hero-stat-label">Veículos disponíveis</span>
                    </div>
                    @if($statClientes)
                    <div class="hero-stat">
                        <span class="hero-stat-value">{{ $statClientes }}</span>
                        <span class="hero-stat-label">Clientes atendidos</span>
                    </div>
                    @endif
                    @if($statAnos)
                    <div class="hero-stat">
                        <span class="hero-stat-value">{{ $statAnos }}</span>
                        <span class="hero-stat-label">Anos no mercado</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right: Decorative --}}
            <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center">
                <div style="width:100%;max-width:480px;aspect-ratio:4/3;background:var(--accent-subtle);border-radius:var(--card-radius);display:flex;align-items:center;justify-content:center;border:1px dashed var(--border-2)">
                    <div style="text-align:center;color:var(--text-3)">
                        <i class="fas fa-car" style="font-size:4rem;margin-bottom:12px;display:block;color:var(--accent);opacity:.35"></i>
                        <span style="font-size:.85rem;letter-spacing:.08em;text-transform:uppercase;opacity:.5">Seu próximo veículo</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     FEATURED VEHICLES
     ═══════════════════════════════════════════════ --}}
<section class="py-5 bg-site-alt">
    <div class="container">
        <div class="row mb-4 align-items-end">
            <div class="col-md-8">
                <div class="section-eyebrow">DESTAQUES DA SEMANA</div>
                <h2 class="section-title">Veículos em <span>Destaque</span></h2>
                <p class="section-subtitle">Selecionamos os melhores veículos para você.</p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <a href="{{ route('site.vehicles.index') }}" class="btn-ver-detalhes" style="display:inline-block;max-width:220px">
                    Ver todo o estoque <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        @if($destaques->isNotEmpty())
            <div class="row">
                @foreach($destaques as $vehicle)
                    @include('site.partials._vehicle_card', ['vehicle' => $vehicle])
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-car"></i>
                <h4>Nenhum destaque no momento</h4>
                <p>Confira todo o nosso estoque disponível.</p>
                <a href="{{ route('site.vehicles.index') }}" class="btn-ver-detalhes" style="display:inline-block;max-width:200px;margin-top:12px">
                    Ver estoque
                </a>
            </div>
        @endif
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     DIFFERENTIALS
     ═══════════════════════════════════════════════ --}}
<section class="py-5 bg-site" id="sobre">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-eyebrow" style="justify-content:center">POR QUE NOS ESCOLHER</div>
            <h2 class="section-title">Nossas <span>Vantagens</span></h2>
            <p class="section-subtitle">Comprometidos com transparência e satisfação em cada negociação.</p>
        </div>
        <div class="row">
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="diff-card">
                    <div class="diff-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="diff-title">Procedência Garantida</div>
                    <p class="diff-text">Todos os veículos com histórico verificado e documentação em dia.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="diff-card">
                    <div class="diff-icon"><i class="fas fa-handshake"></i></div>
                    <div class="diff-title">Negociação Transparente</div>
                    <p class="diff-text">Sem letras miúdas. Preço justo e condições claras desde o início.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="diff-card">
                    <div class="diff-icon"><i class="fas fa-money-check-alt"></i></div>
                    <div class="diff-title">Facilidade no Financiamento</div>
                    <p class="diff-text">Trabalhamos com as principais financeiras do mercado.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="diff-card">
                    <div class="diff-icon"><i class="fas fa-star"></i></div>
                    <div class="diff-title">Avaliação Gratuita</div>
                    <p class="diff-text">Traga seu veículo para avaliação sem compromisso.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     BROWSE BY CATEGORY
     ═══════════════════════════════════════════════ --}}
<section class="py-5 bg-site-alt">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-eyebrow" style="justify-content:center">NOSSO ESTOQUE</div>
            <h2 class="section-title">Buscar por <span>Categoria</span></h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('site.vehicles.index') }}?categoria=hatch" class="cat-card">
                    <i class="fas fa-car"></i>
                    <span class="cat-card-label">Hatch</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('site.vehicles.index') }}?categoria=sedan" class="cat-card">
                    <i class="fas fa-car-side"></i>
                    <span class="cat-card-label">Sedan</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('site.vehicles.index') }}?categoria=suv" class="cat-card">
                    <i class="fas fa-truck-pickup"></i>
                    <span class="cat-card-label">SUV</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('site.vehicles.index') }}?categoria=pickup" class="cat-card">
                    <i class="fas fa-truck"></i>
                    <span class="cat-card-label">Pickup</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('site.vehicles.index') }}?categoria=van" class="cat-card">
                    <i class="fas fa-shuttle-van"></i>
                    <span class="cat-card-label">Van</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('site.vehicles.index') }}?categoria=esportivo" class="cat-card">
                    <i class="fas fa-flag-checkered"></i>
                    <span class="cat-card-label">Esportivo</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CTA BANNER
     ═══════════════════════════════════════════════ --}}
<section class="cta-banner">
    <div class="container" style="position:relative;z-index:1">
        <div class="cta-banner-title">Não encontrou o que procurava?</div>
        <p class="cta-banner-subtitle">Temos um estoque completo com as melhores opções para você.</p>
        <a href="{{ route('site.vehicles.index') }}" class="btn-cta-white">
            <i class="fas fa-list mr-2"></i>Ver Estoque Completo
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     MAP + CONTACT
     ═══════════════════════════════════════════════ --}}
<section class="py-5 bg-site">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-eyebrow" style="justify-content:center">ONDE ESTAMOS</div>
            <h2 class="section-title">Como <span>Nos Encontrar</span></h2>
        </div>
        <div class="row align-items-stretch">
            {{-- Map --}}
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="map-section" style="height:360px">
                    <iframe
                        src="{{ $mapsEmbedUrl }}"
                        width="100%"
                        height="100%"
                        style="border:0;display:block"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Localização {{ $nomeSistema }}">
                    </iframe>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="col-lg-5">
                <div class="contact-info-card">
                    <h4 style="font-family:var(--font-heading);font-weight:700;color:var(--text);margin-bottom:20px">Informações de Contato</h4>

                    @if($telefone)
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
                        <div class="contact-info-text">
                            <span class="contact-info-label">Telefone</span>
                            <span class="contact-info-value">{{ $telefone }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fab fa-whatsapp"></i></div>
                        <div class="contact-info-text">
                            <span class="contact-info-label">WhatsApp</span>
                            <span class="contact-info-value">
                                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">
                                    {{ $telefone ?: '(27) ' . substr($whatsapp, -9, 5) . '-' . substr($whatsapp, -4) }}
                                </a>
                            </span>
                        </div>
                    </div>

                    @php $localDisplay = $enderecoCompleto ?: $cidadeEstado; @endphp
                    @if($localDisplay)
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="contact-info-text">
                            <span class="contact-info-label">Localização</span>
                            <span class="contact-info-value">{{ $localDisplay }}</span>
                        </div>
                    </div>
                    @endif

                    @if($horarioAten)
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
                        <div class="contact-info-text">
                            <span class="contact-info-label">Horário de Atendimento</span>
                            <span class="contact-info-value">{{ $horarioAten }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Olá! Gostaria de mais informações sobre os veículos da ' . $nomeSistema . '.') }}"
                           target="_blank"
                           rel="noopener"
                           class="btn-whatsapp-cta">
                            <i class="fab fa-whatsapp"></i>
                            Chamar no WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
