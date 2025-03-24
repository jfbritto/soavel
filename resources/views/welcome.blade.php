<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Soavel Veículos | Carros Seminovos em Santa Maria de Jetibá - ES</title>
    <meta name="author" content="Soavel Veículos" />
    <meta name="description"
        content="Encontre carros seminovos de qualidade em Santa Maria de Jetibá-ES. HB20, Onix, Voyage e muito mais com condições especiais. Confira nosso estoque!">
    <meta name="keywords"
        content="carros seminovos, carros usados, comprar carro Santa Maria de Jetibá, HB20 seminovo, Onix usado, Strada seminova, concessionária Santa Maria de Jetibá">
    <link rel="canonical" href="https://soavelveiculos.com.br/">
    <link rel="icon" type="image/x-icon" href="/img/logo/soavel-fundo.svg" />

    <!-- JSON-LD Schema.org -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "AutoDealer",
          "name": "Soavel Veículos",
          "image": "https://soavelveiculos.com.br/img/logo/soavel-fundo.png",
          "@id": "https://soavelveiculos.com.br/",
          "url": "https://soavelveiculos.com.br/",
          "telephone": "+5527998490472",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "Rod. Galerano Afonso Venturini, 2045 - São Luiz",
            "addressLocality": "Santa Maria de Jetibá",
            "addressRegion": "ES",
            "addressCountry": "BR"
          },
          "openingHours": "Mo-Sa 09:00-18:00"
        }
    </script>

    <!-- Open Graph -->
    <meta property="og:title" content="Soavel Veículos - Carros Seminovos em Santa Maria de Jetibá - ES">
    <meta property="og:description"
        content="Compre seminovos com segurança e qualidade. Veja nossas ofertas especiais!">
    <meta property="og:image" content="https://soavelveiculos.com.br/img/logo/soavel-fundo.png">
    <meta property="og:url" content="https://soavelveiculos.com.br/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .logo {
            max-height: 60px;
        }

        .card img {
            height: 200px;
            object-fit: cover;
        }

        body {
            padding-top: 100px;
            /* altura original da navbar, ajuste conforme necessário */
        }

        .navbar {
            transition: all 0.3s ease-in-out;
            padding: 20px 10px;
            /* Navbar originalmente maior */
        }

        .navbar-scrolled {
            padding: 5px 10px;
            /* Navbar mais fina após scroll */
        }

        .logo {
            transition: all 0.3s ease-in-out;
            height: 60px;
            /* altura original da logo */
        }

        .navbar-scrolled .logo {
            height: 40px;
            /* altura menor da logo após scroll */
        }

        .shadow-custom {
            box-shadow: 0px 4px 8px rgba(2, 52, 93, 0.3);
            border-bottom: 3px solid #02345d;
        }

        .map-container {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%;
            /* proporção 16:9 */
            height: 0;
        }

        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body style="color: #02345d">

    <!-- Cabeçalho e Navegação -->
    <nav id="navbar"
        class="navbar navbar-expand-lg navbar-light bg-light justify-content-center shadow-custom fixed-top">
        <a class="navbar-brand mx-auto" href="#">
            <img src="/img/logo/soavel-fundo.png" alt="Logo SOAVEL VEÍCULOS" class="logo">
        </a>
    </nav>

    <!-- Banner Principal -->
    <div class="jumbotron text-center mt-2">
        <h2>Encontre o veículo dos seus sonhos aqui!</h2>
        <p class="lead">Com as melhores condições do mercado.</p>
        {{-- <a class="btn btn-primary btn-lg" href="#estoque" role="button">Ver estoque</a> --}}
    </div>

    <!-- Seção Destaques -->
    <div class="container">
        <h2 class="text-center my-4">Destaques</h2>
        <div class="row">

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator1" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator1" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator1" data-slide-to="2"></li>
                            <li data-target="#CarIndicator1" data-slide-to="3"></li>
                            <li data-target="#CarIndicator1" data-slide-to="4"></li>
                            <li data-target="#CarIndicator1" data-slide-to="5"></li>
                            <li data-target="#CarIndicator1" data-slide-to="6"></li>
                            <li data-target="#CarIndicator1" data-slide-to="7"></li>
                            <li data-target="#CarIndicator1" data-slide-to="8"></li>
                            <li data-target="#CarIndicator1" data-slide-to="9"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-4.jpeg" alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-5.jpeg" alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-6.jpeg" alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-7.jpeg" alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-8.jpeg" alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/hb20-1/hb20-1-9.jpeg" alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator1" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator1" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Hyundai HB20 1.0 Evolution</h5>
                        <p class="card-text">Ano: 2022 | Km: 13.500 | Manual</p>
                        <p class="font-weight-bold">R$ 69.900</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator2" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator2" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator2" data-slide-to="2"></li>
                            <li data-target="#CarIndicator2" data-slide-to="3"></li>
                            <li data-target="#CarIndicator2" data-slide-to="4"></li>
                            <li data-target="#CarIndicator2" data-slide-to="5"></li>
                            <li data-target="#CarIndicator2" data-slide-to="6"></li>
                            <li data-target="#CarIndicator2" data-slide-to="7"></li>
                            <li data-target="#CarIndicator2" data-slide-to="8"></li>
                            <li data-target="#CarIndicator2" data-slide-to="9"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-4.jpeg" alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-5.jpeg" alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-6.jpeg" alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-7.jpeg" alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-8.jpeg" alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-1/onix-1-9.jpeg" alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator2" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator2" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Chevrolet Onix 1.0 LT aspirado</h5>
                        <p class="card-text">Ano: 22/23 | Km: 59.830 | Manual</p>
                        <p class="font-weight-bold">R$ 72.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator3" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator3" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator3" data-slide-to="2"></li>
                            <li data-target="#CarIndicator3" data-slide-to="3"></li>
                            <li data-target="#CarIndicator3" data-slide-to="4"></li>
                            <li data-target="#CarIndicator3" data-slide-to="5"></li>
                            <li data-target="#CarIndicator3" data-slide-to="6"></li>
                            <li data-target="#CarIndicator3" data-slide-to="7"></li>
                            <li data-target="#CarIndicator3" data-slide-to="8"></li>
                            <li data-target="#CarIndicator3" data-slide-to="9"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-2.jpeg"
                                    alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-4.jpeg"
                                    alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-5.jpeg"
                                    alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-6.jpeg"
                                    alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-7.jpeg"
                                    alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-8.jpeg"
                                    alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/voyage-1/voyage-1-9.jpeg"
                                    alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator3" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator3" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">VW Voyage 1.6 MB5</h5>
                        <p class="card-text">Ano: 19/20 | Km: 89.540 | Manual</p>
                        <p class="font-weight-bold">R$ 53.500</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator4" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator4" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator4" data-slide-to="2"></li>
                            <li data-target="#CarIndicator4" data-slide-to="3"></li>
                            <li data-target="#CarIndicator4" data-slide-to="4"></li>
                            <li data-target="#CarIndicator4" data-slide-to="5"></li>
                            <li data-target="#CarIndicator4" data-slide-to="6"></li>
                            <li data-target="#CarIndicator4" data-slide-to="7"></li>
                            <li data-target="#CarIndicator4" data-slide-to="8"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-2.jpeg"
                                    alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-4.jpeg"
                                    alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-5.jpeg"
                                    alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-6.jpeg"
                                    alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-7.jpeg"
                                    alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/strada-1/strada-1-8.jpeg"
                                    alt="Oitavo Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator4" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator4" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Fiat Strada 1.3 Endurance</h5>
                        <p class="card-text">Ano: 23/24 | Km: 44.685 | Manual</p>
                        <p class="font-weight-bold">R$ 85.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator5" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator5" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator5" data-slide-to="2"></li>
                            <li data-target="#CarIndicator5" data-slide-to="3"></li>
                            <li data-target="#CarIndicator5" data-slide-to="4"></li>
                            <li data-target="#CarIndicator5" data-slide-to="5"></li>
                            <li data-target="#CarIndicator5" data-slide-to="6"></li>
                            <li data-target="#CarIndicator5" data-slide-to="7"></li>
                            <li data-target="#CarIndicator5" data-slide-to="8"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-2.jpeg"
                                    alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-4.jpeg"
                                    alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-5.jpeg"
                                    alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-6.jpeg"
                                    alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-7.jpeg"
                                    alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fiesta-1/fiesta-1-8.jpeg"
                                    alt="Oitavo Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator5" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator5" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Ford Fiesta 1.0</h5>
                        <p class="card-text">Ano: 12/13 | Km: 130.850 | Manual</p>
                        <p class="font-weight-bold">R$ 28.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator6" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator6" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator6" data-slide-to="2"></li>
                            <li data-target="#CarIndicator6" data-slide-to="3"></li>
                            <li data-target="#CarIndicator6" data-slide-to="4"></li>
                            <li data-target="#CarIndicator6" data-slide-to="5"></li>
                            <li data-target="#CarIndicator6" data-slide-to="6"></li>
                            <li data-target="#CarIndicator6" data-slide-to="7"></li>
                            <li data-target="#CarIndicator6" data-slide-to="8"></li>
                            <li data-target="#CarIndicator6" data-slide-to="9"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-4.jpeg" alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-5.jpeg" alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-6.jpeg" alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-7.jpeg" alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-8.jpeg" alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-2/onix-2-9.jpeg" alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator6" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator6" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Chevrolet Onix 1.0 Joy</h5>
                        <p class="card-text">Ano: 19/20 | Km: 76.697 | Manual</p>
                        <p class="font-weight-bold">R$ 53.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator7" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator7" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator7" data-slide-to="2"></li>
                            <li data-target="#CarIndicator7" data-slide-to="3"></li>
                            <li data-target="#CarIndicator7" data-slide-to="4"></li>
                            <li data-target="#CarIndicator7" data-slide-to="5"></li>
                            <li data-target="#CarIndicator7" data-slide-to="6"></li>
                            <li data-target="#CarIndicator7" data-slide-to="7"></li>
                            <li data-target="#CarIndicator7" data-slide-to="8"></li>
                            <li data-target="#CarIndicator7" data-slide-to="9"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-4.jpeg" alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-5.jpeg" alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-6.jpeg" alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-7.jpeg" alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-8.jpeg" alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/onix-3/onix-3-9.jpeg" alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator7" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator7" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Chevrolet Onix 1.0 LT aspirado</h5>
                        <p class="card-text">Ano: 22/23 | Km: 45.498 | Manual</p>
                        <p class="font-weight-bold">R$ 72.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator11" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator11" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator11" data-slide-to="2"></li>
                            <li data-target="#CarIndicator11" data-slide-to="3"></li>
                            <li data-target="#CarIndicator11" data-slide-to="4"></li>
                            <li data-target="#CarIndicator11" data-slide-to="5"></li>
                            <li data-target="#CarIndicator11" data-slide-to="6"></li>
                            <li data-target="#CarIndicator11" data-slide-to="7"></li>
                            <li data-target="#CarIndicator11" data-slide-to="8"></li>
                            <li data-target="#CarIndicator11" data-slide-to="9"></li>
                            <li data-target="#CarIndicator11" data-slide-to="10"></li>
                            <li data-target="#CarIndicator11" data-slide-to="11"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-4.jpeg" alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-5.jpeg" alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-6.jpeg" alt="Sexto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-7.jpeg" alt="Setimo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-8.jpeg" alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-9.jpeg" alt="Nono Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-10.jpeg" alt="Nono Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/compass-1/compass-1-11.jpeg" alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator11" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator11" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Jeep Compass</h5>
                        <p class="card-text">Ano: 2017 | Km: | Automático</p>
                        <p class="font-weight-bold">R$ 120.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator8" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator8" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator8" data-slide-to="2"></li>
                            <li data-target="#CarIndicator8" data-slide-to="3"></li>
                            <li data-target="#CarIndicator8" data-slide-to="4"></li>
                            <li data-target="#CarIndicator8" data-slide-to="5"></li>
                            <li data-target="#CarIndicator8" data-slide-to="6"></li>
                            {{-- <li data-target="#CarIndicator8" data-slide-to="7"></li> --}}
                            <li data-target="#CarIndicator8" data-slide-to="8"></li>
                            <li data-target="#CarIndicator8" data-slide-to="9"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-1.jpeg"
                                    alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-2.jpeg"
                                    alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-3.jpeg"
                                    alt="Terceiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-4.jpeg"
                                    alt="Querto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-5.jpeg"
                                    alt="Quinto Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-6.jpeg" alt="Sexto Slide">
                            </div>
                            {{-- <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-7.jpeg"
                                    alt="Setimo Slide">
                            </div> --}}
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-8.jpeg"
                                    alt="Oitavo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/fusca-1/fusca-1-9.jpeg" alt="Nono Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator8" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator8" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">VW Fusca 1600</h5>
                        <p class="card-text">Ano: 1984 | Km: 85.776 | Manual</p>
                        <p class="font-weight-bold">R$ 33.000</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator9" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator9" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator9" data-slide-to="2"></li>
                            <li data-target="#CarIndicator9" data-slide-to="3"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/cg-1/cg-1-1.jpeg" alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/cg-1/cg-1-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/cg-1/cg-1-3.jpeg" alt="Terceiro Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator9" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator9" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Honda CG 125c</h5>
                        <p class="card-text">Ano: 2002 | Km: 42.196</p>
                        <p class="font-weight-bold">R$ 10.200</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

            <!-- Carro Slide -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div id="CarIndicator10" class="carousel slide card-img-top" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-target="#CarIndicator10" data-slide-to="1" class="active"></li>
                            <li data-target="#CarIndicator10" data-slide-to="2"></li>
                            <li data-target="#CarIndicator10" data-slide-to="3"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100" src="/img/veiculos/biz-1/biz-1-1.jpeg" alt="Primeiro Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/biz-1/biz-1-2.jpeg" alt="Segundo Slide">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100" src="/img/veiculos/biz-1/biz-1-3.jpeg" alt="Terceiro Slide">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#CarIndicator10" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#CarIndicator10" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Honda Biz 125c</h5>
                        <p class="card-text">Ano: 20/21 | Km: 22.941 | Manual</p>
                        <p class="font-weight-bold">R$ 16.200</p>
                        <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i>
                            Gostei desse</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="map-container">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4417.9692150682395!2d-40.74408482403657!3d-20.001308640534603!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xb77d27c247513d%3A0xc155ed404cf6cdcd!2sSoavel%20Ve%C3%ADculos!5e1!3m2!1spt-BR!2sbr!4v1742262481187!5m2!1spt-BR!2sbr"
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <!-- Rodapé -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>Telefone: <a
                href="https://api.whatsapp.com/send?phone=5527998490472&text=Olá Adolfo, pode me ajudar a encontrar um novo veículo?">(27)
                99849-0472</a></p>
        <p>Horário: Segunda a Sábado, 9h às 18h</p>
        <a href="https://www.facebook.com/adolfo.busteke.1/" class="text-white">Facebook</a> |
        <a href="https://www.instagram.com/soavel_veiculos" class="text-white">Instagram</a>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.addEventListener('scroll', function () {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    document.addEventListener('click', function(e) {
    const target = e.target.closest('.btn-whatsapp');

    if (target) {
        e.preventDefault();

        const card = target.closest('.card-body');
        const titulo = card.querySelector('.card-title').textContent.trim();
        const text = card.querySelector('.card-text').textContent.trim();
        const valor = card.querySelector('.font-weight-bold').textContent.trim();

        const mensagem = encodeURIComponent(`Olá Adolfo, o veículo: ${titulo} | ${text} | ${valor} está disponível?`);

        const url = `https://api.whatsapp.com/send?phone=5527998490472&text=${mensagem}`;

        window.open(url, '_blank');
    }
    });

    </script>
</body>

</html>
