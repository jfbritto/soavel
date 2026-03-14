@extends('layouts.site')

@section('meta_title', $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' | Soavel Veículos')
@section('meta_description', 'Compre ' . $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . '. ' . $vehicle->km_formatado . ', ' . ucfirst($vehicle->combustivel) . '. Soavel Veículos, Santa Maria de Jetibá - ES.')
@if($vehicle->principalPhoto)
@section('og_image', $vehicle->principalPhoto->url)
@endif

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Car",
    "name": "{{ $vehicle->titulo }}",
    "brand": { "@type": "Brand", "name": "{{ $vehicle->marca }}" },
    "model": "{{ $vehicle->modelo }}",
    "vehicleModelDate": "{{ $vehicle->ano_modelo }}",
    "mileageFromOdometer": { "@type": "QuantitativeValue", "value": {{ $vehicle->km }}, "unitCode": "KMT" },
    "color": "{{ $vehicle->cor }}",
    "fuelType": "{{ $vehicle->combustivel }}",
    "offers": {
        "@type": "Offer",
        "priceCurrency": "BRL",
        "price": "{{ $vehicle->preco }}",
        "availability": "{{ $vehicle->status === 'disponivel' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
    },
    "seller": {
        "@type": "AutoDealer",
        "name": "Soavel Veículos",
        "telephone": "+5527998490472",
        "address": { "@type": "PostalAddress", "addressLocality": "Santa Maria de Jetibá", "addressRegion": "ES", "addressCountry": "BR" }
    }
}
</script>
@endsection

@section('content')
<div class="container py-4 py-md-5 vehicle-detail-pb">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('site.vehicles.index') }}">Estoque</a></li>
            <li class="breadcrumb-item active">{{ $vehicle->titulo }}</li>
        </ol>
    </nav>

    @if(session('contact_success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('contact_success') }}
    </div>
    @endif

    <div class="row">
        <!-- Galeria + Info -->
        <div class="col-lg-8">

            <!-- Galeria de fotos -->
            <div class="mb-4">
                @if($vehicle->photos->isNotEmpty())
                    <!-- Foto principal -->
                    <a href="{{ $vehicle->photos->first()->url }}" class="glightbox" data-gallery="vehicle-{{ $vehicle->id }}">
                        <img src="{{ $vehicle->photos->first()->url }}" alt="{{ $vehicle->titulo }}"
                             class="vehicle-gallery-main rounded shadow">
                    </a>
                    <!-- Thumbnails -->
                    @if($vehicle->photos->count() > 1)
                    <div class="row mt-2 mx-0">
                        @foreach($vehicle->photos->skip(1) as $photo)
                        <div class="col-4 col-sm-3 p-1">
                            <a href="{{ $photo->url }}" class="glightbox" data-gallery="vehicle-{{ $vehicle->id }}">
                                <img src="{{ $photo->url }}" alt="{{ $vehicle->titulo }}" class="vehicle-thumb-img rounded">
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center vehicle-gallery-nophoto">
                        <i class="fas fa-car fa-5x text-muted"></i>
                    </div>
                @endif
            </div>

            <!-- Compartilhar -->
            <div class="d-flex align-items-center mb-4" style="gap:10px">
                <span class="text-muted font-weight-600" style="font-size:.85rem"><i class="fas fa-share-alt mr-1"></i> Compartilhar:</span>
                <a href="https://wa.me/?text={{ urlencode($vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . ' - Veja no site: ' . request()->url()) }}"
                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Compartilhar no WhatsApp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Compartilhar no Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado) }}&url={{ urlencode(request()->url()) }}"
                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Compartilhar no X/Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyVehicleLink()" title="Copiar link">
                    <i class="fas fa-link"></i>
                </button>
                <span id="copy-feedback" class="text-success font-weight-600" style="font-size:.8rem;display:none">Copiado!</span>
            </div>

            <!-- Especificações -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="font-weight-800" style="color:var(--azul)">{{ $vehicle->titulo }}</h2>
                    <div class="d-flex flex-wrap mb-3" style="gap:8px">
                        <span class="badge badge-primary px-3 py-2">{{ ucfirst($vehicle->categoria) }}</span>
                        <span class="badge badge-{{ $vehicle->status_color }} px-3 py-2">{{ $vehicle->status_label }}</span>
                        @if($vehicle->destaque)<span class="badge badge-warning px-3 py-2">⭐ Destaque</span>@endif
                    </div>

                    <div class="row">
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-calendar-alt fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</div>
                            <small class="text-muted">Ano Fab/Mod</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-tachometer-alt fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->km_formatado }}</div>
                            <small class="text-muted">Quilometragem</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-gas-pump fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ ucfirst($vehicle->combustivel) }}</div>
                            <small class="text-muted">Combustível</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-cog fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ ucfirst($vehicle->transmissao) }}</div>
                            <small class="text-muted">Transmissão</small>
                        </div>
                        @if($vehicle->motorizacao)
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-engine fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->motorizacao }}</div>
                            <small class="text-muted">Motor</small>
                        </div>
                        @endif
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-palette fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->cor }}</div>
                            <small class="text-muted">Cor</small>
                        </div>
                        @if($vehicle->portas > 0)
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-door-closed fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->portas }}</div>
                            <small class="text-muted">Portas</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            @if($vehicle->descricao)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="font-weight-700 mb-3">Descrição</h5>
                    <p class="mb-0">{!! nl2br(e($vehicle->descricao)) !!}</p>
                </div>
            </div>
            @endif

            <!-- Opcionais -->
            @if($vehicle->features->isNotEmpty())
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="font-weight-700 mb-3">Opcionais e Equipamentos</h5>
                    <div class="row">
                        @foreach($vehicle->features as $feat)
                        <div class="col-md-4 col-6 mb-2">
                            <i class="fas fa-check-circle text-success mr-1"></i>{{ $feat->feature }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar CTA -->
        <div class="col-lg-4">
            <div style="position:sticky;top:90px">
                {{-- Preço + Status --}}
                <div class="card shadow-sm mb-3 border-0" style="border-radius:12px;overflow:hidden">
                    <div style="background:#1e3a5f;color:#ffffff;padding:24px 20px;text-align:center">
                        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;opacity:.8;margin-bottom:4px">Preço</div>
                        <div style="font-size:clamp(1.5rem,6vw,2.2rem);font-weight:800;color:#ffffff;line-height:1.1">{{ $vehicle->preco_formatado }}</div>
                        @if($vehicle->status !== 'disponivel')
                        <span class="badge badge-{{ $vehicle->status_color }} mt-2" style="font-size:.85rem">{{ $vehicle->status_label }}</span>
                        @endif
                    </div>

                    <div class="card-body" style="padding:20px">
                        {{-- Resumo rápido --}}
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-calendar-alt mr-1"></i>Ano</span>
                            <strong>{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-tachometer-alt mr-1"></i>KM</span>
                            <strong>{{ $vehicle->km_formatado }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-cog mr-1"></i>Câmbio</span>
                            <strong>{{ ucfirst($vehicle->transmissao) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                            <strong>{{ ucfirst($vehicle->combustivel) }}</strong>
                        </div>

                        {{-- Botão WhatsApp --}}
                        <a href="https://wa.me/{{ config('app.whatsapp_number', '5527998490472') }}?text={{ urlencode('Olá! Tenho interesse no ' . $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . '. Vi no site da Soavel Veículos. Podemos conversar?') }}"
                           target="_blank" class="btn btn-whatsapp btn-block btn-lg mb-2" style="font-size:1rem;padding:12px">
                            <i class="fab fa-whatsapp mr-2"></i>Quero este veículo!
                        </a>

                        {{-- Ligar --}}
                        <a href="tel:+{{ config('app.whatsapp_number', '5527998490472') }}" class="btn btn-outline-secondary btn-block mb-0" style="font-size:.9rem">
                            <i class="fas fa-phone-alt mr-1"></i>Ligar agora
                        </a>
                    </div>
                </div>

                {{-- Formulário de contato --}}
                <div class="card shadow-sm mb-4 border-0" style="border-radius:12px">
                    <div class="card-body" style="padding:20px">
                        <h6 class="font-weight-700 mb-1"><i class="fas fa-envelope mr-1" style="color:var(--azul)"></i>Solicitar Informações</h6>
                        <p class="text-muted mb-3" style="font-size:.8rem">Preencha e entraremos em contato</p>

                        @if(session('contact_success'))
                        <div class="alert alert-success p-2">{{ session('contact_success') }}</div>
                        @else
                        <form action="{{ route('site.interesse.store', $vehicle) }}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <input type="text" name="nome" class="form-control form-control-sm" placeholder="Seu nome *" required value="{{ old('nome') }}">
                            </div>
                            <div class="form-group mb-2">
                                <input type="text" name="telefone" class="form-control form-control-sm" placeholder="Seu WhatsApp *" required value="{{ old('telefone') }}">
                            </div>
                            <div class="form-group mb-2">
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="E-mail (opcional)" value="{{ old('email') }}">
                            </div>
                            <div class="form-group mb-2">
                                <textarea name="mensagem" class="form-control form-control-sm" rows="2" placeholder="Mensagem (opcional)">{{ old('mensagem') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary-site btn-block text-white" style="font-size:.9rem">
                                <i class="fas fa-paper-plane mr-1"></i>Enviar Interesse
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Veículos similares -->
    @if($similares->isNotEmpty())
    <div class="mt-5">
        <h4 class="font-weight-800 mb-4" style="color:var(--azul)">Veículos Similares</h4>
        <div class="row">
            @foreach($similares as $vehicle)
                @include('site.partials._vehicle_card', ['vehicle' => $vehicle])
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Sticky CTA bar — mobile only --}}
@if($vehicle->status === 'disponivel')
<div class="mobile-cta-bar d-lg-none">
    <div class="mobile-cta-bar__price">{{ $vehicle->preco_formatado }}</div>
    <a href="https://wa.me/{{ config('app.whatsapp_number', '5527998490472') }}?text={{ urlencode('Olá! Tenho interesse no ' . $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . '. Vi no site. Podemos conversar?') }}"
       target="_blank" rel="noopener" class="mobile-cta-bar__btn">
        <i class="fab fa-whatsapp"></i> Tenho interesse!
    </a>
</div>
@endif

<script>
// Esconde o botão flutuante do WhatsApp quando a barra CTA mobile está presente
if (document.querySelector('.mobile-cta-bar')) {
    document.body.classList.add('has-mobile-cta');
}

function copyVehicleLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        var fb = document.getElementById('copy-feedback');
        fb.style.display = 'inline';
        setTimeout(function() { fb.style.display = 'none'; }, 2000);
    });
}
</script>
@endsection
