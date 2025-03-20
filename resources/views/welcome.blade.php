<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Plataforma de venda de veículos" />
    <meta name="author" content="Soavel Veículos" />
    <title>Soavel Veículos</title>
    <link rel="icon" type="image/x-icon" href="/img/logo/soavel-fundo.svg" />
    <meta name="keywords" content="venda de carros" />

    <!-- Meta Tags Open Graph para compartilhamento em redes sociais -->
    <meta property="og:title" content="Soavel Veículos">
    <meta property="og:description" content="Encontre o veículo dos seus sonhos aqui">
    <meta property="og:image" content="/img/logo/soavel-fundo.png">
    <meta property="og:url" content="https://soavelveiculos.com.br/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .logo { max-height: 60px; }
    .card img { height: 200px; object-fit: cover; }

    body {
    padding-top: 100px; /* altura original da navbar, ajuste conforme necessário */
    }

    .navbar {
    transition: all 0.3s ease-in-out;
    padding: 20px 10px; /* Navbar originalmente maior */
    }

    .navbar-scrolled {
    padding: 5px 10px; /* Navbar mais fina após scroll */
    }

    .logo {
    transition: all 0.3s ease-in-out;
    height: 60px; /* altura original da logo */
    }

    .navbar-scrolled .logo {
    height: 40px; /* altura menor da logo após scroll */
    }

    .shadow-custom {
    box-shadow: 0px 4px 8px rgba(2, 52, 93, 0.3);
    border-bottom: 3px solid #02345d;
    }

    .map-container {
    position: relative;
    overflow: hidden;
    padding-top: 56.25%; /* proporção 16:9 */
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
  <nav id="navbar" class="navbar navbar-expand-lg navbar-light bg-light justify-content-center shadow-custom fixed-top">
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
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-1.jpeg" alt="Primeiro Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-2.jpeg" alt="Segundo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-3.jpeg" alt="Terceiro Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-4.jpeg" alt="Querto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-5.jpeg" alt="Quinto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-6.jpeg" alt="Sexto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-7.jpeg" alt="Setimo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-8.jpeg" alt="Oitavo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/hb20-1-1/hb20-1-9.jpeg" alt="Nono Slide">
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
            <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Gostei desse</a>
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
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-1.jpeg" alt="Primeiro Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-2.jpeg" alt="Segundo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-3.jpeg" alt="Terceiro Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-4.jpeg" alt="Querto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-5.jpeg" alt="Quinto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-6.jpeg" alt="Sexto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-7.jpeg" alt="Setimo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-8.jpeg" alt="Oitavo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/onix-1-1/onix-1-9.jpeg" alt="Nono Slide">
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
            <h5 class="card-title">Chevrolet Onix LT 1.0 aspirado</h5>
            <p class="card-text">Ano: 22/23 | Km: 59.830 | Manual</p>
            <p class="font-weight-bold">R$ 72.000</p>
            <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Gostei desse</a>
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
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-1.jpeg" alt="Primeiro Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-2.jpeg" alt="Segundo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-3.jpeg" alt="Terceiro Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-4.jpeg" alt="Querto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-5.jpeg" alt="Quinto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-6.jpeg" alt="Sexto Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-7.jpeg" alt="Setimo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-8.jpeg" alt="Oitavo Slide">
              </div>
              <div class="carousel-item">
                <img class="d-block w-100" src="/img/carros/voyage-1-1/voyage-1-9.jpeg" alt="Nono Slide">
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
            <h5 class="card-title">VW Voyage MB5 1.6</h5>
            <p class="card-text">Ano: 19/20 | Km: 89.540 | Manual</p>
            <p class="font-weight-bold">R$ 53.500</p>
            <a href="#" class="btn btn-success btn-block btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Gostei desse</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="map-container">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4417.9692150682395!2d-40.74408482403657!3d-20.001308640534603!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xb77d27c247513d%3A0xc155ed404cf6cdcd!2sSoavel%20Ve%C3%ADculos!5e1!3m2!1spt-BR!2sbr!4v1742262481187!5m2!1spt-BR!2sbr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
  <!-- Rodapé -->
  <footer class="bg-dark text-white text-center py-3 mt-4">
    <p>Telefone: <a href="https://api.whatsapp.com/send?phone=5527998490472&text=Olá, estou procurando um veículo!">(27) 99849-0472</a></p>
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
        e.preventDefault(); // evita comportamento padrão

        const card = target.closest('.card-body');
        const titulo = card.querySelector('.card-title').textContent.trim();

        const mensagem = encodeURIComponent(`Gostaria de mais informações sobre o veículo: ${titulo}`);

        const url = `https://api.whatsapp.com/send?phone=5527998490472&text=${mensagem}`;

        window.open(url, '_blank');
    }
    });

  </script>
</body>
</html>
