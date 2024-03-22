<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Site de venda de carros usados. Confira nossa seleção de carros em destaque e encontre o carro perfeito para você.">
  <meta name="keywords" content="carros usados, venda de carros, carros seminovos">
  <meta name="robots" content="index, follow">
  <title>Venda de Carros Usados - Encontre o seu carro perfeito | Carros Usados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .carousel-item img {
      height: 300px; /* ajuste a altura conforme necessário */
      object-fit: cover;
    }
  </style>
</head>
<body>

<!-- Barra de Navegação -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Carros Usados</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Sobre</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contato</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Seção de Destaque -->
<section class="py-5 text-center bg-light">
  <div class="container">
    <h2 class="display-4">Carros em Destaque</h2>
    <p class="lead">Confira nossa seleção especial de carros em destaque.</p>
    <!-- Carrossel de Destaque -->
    <div id="carouselDestaque" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="Carro em Destaque 1">
          <div class="carousel-caption d-none d-md-block">
            <h5>Carro em Destaque 1</h5>
            <p>Descrição breve do carro em destaque 1.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="Carro em Destaque 2">
          <div class="carousel-caption d-none d-md-block">
            <h5>Carro em Destaque 2</h5>
            <p>Descrição breve do carro em destaque 2.</p>
          </div>
        </div>
        <!-- Adicione mais itens de carrossel conforme necessário -->
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestaque" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselDestaque" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Próximo</span>
      </button>
    </div>
  </div>
</section>

<!-- Seção de Listagem de Carros -->
<section class="py-5">
  <div class="container">
    <h2 class="display-4 text-center mb-5">Carros Disponíveis</h2>
    <div class="row">
      <!-- Exemplo de card de carro -->
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
          <div id="carouselCarro1" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="https://via.placeholder.com/300" class="d-block w-100" alt="Carro 1 Foto 1">
              </div>
              <div class="carousel-item">
                <img src="https://via.placeholder.com/300" class="d-block w-100" alt="Carro 1 Foto 2">
              </div>
              <!-- Adicione mais fotos conforme necessário -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselCarro1" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselCarro1" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Próximo</span>
            </button>
          </div>
          <div class="card-body">
            <h5 class="card-title">Carro 1</h5>
            <p class="card-text">Descrição breve do carro 1. Pode incluir informações como ano, quilometragem, etc.</p>
            <a href="#" class="btn btn-primary">Ver Detalhes</a>
          </div>
        </div>
      </div>
      <!-- Adicione mais cards de carros conforme necessário -->
    </div>
  </div>
</section>

<!-- Rodapé -->
<footer class="bg-dark text-light py-4">
  <div class="container text-center">
    <p>&copy; 2024 Carros Usados. Todos os direitos reservados.</p>
  </div>
</footer>

<!-- Scripts do Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
