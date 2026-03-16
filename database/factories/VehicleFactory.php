<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition()
    {
        $marca = $this->faker->randomElement(['Toyota', 'Honda', 'Chevrolet', 'Fiat', 'Volkswagen']);
        $modelo = $this->faker->randomElement(['Corolla', 'Civic', 'Onix', 'Argo', 'Gol']);

        $ano = $this->faker->numberBetween(2018, 2025);

        return [
            'marca' => $marca,
            'modelo' => $modelo,
            'versao' => '1.0 Turbo',
            'ano_fabricacao' => $ano,
            'ano_modelo' => $ano,
            'km' => $this->faker->numberBetween(0, 100000),
            'preco' => $this->faker->randomFloat(2, 30000, 200000),
            'preco_compra' => $this->faker->randomFloat(2, 20000, 150000),
            'cor' => $this->faker->randomElement(['Branco', 'Preto', 'Prata', 'Vermelho']),
            'combustivel' => 'flex',
            'transmissao' => 'automatico',
            'portas' => 4,
            'motorizacao' => '1.0',
            'categoria' => 'hatch',
            'status' => 'disponivel',
            'descricao' => $this->faker->sentence(),
            'destaque' => false,
            'slug' => $this->faker->unique()->slug(3),
        ];
    }

    public function disponivel()
    {
        return $this->state(['status' => 'disponivel']);
    }

    public function vendido()
    {
        return $this->state(['status' => 'vendido']);
    }

    public function reservado()
    {
        return $this->state(['status' => 'reservado']);
    }

    public function destaque()
    {
        return $this->state(['destaque' => true]);
    }
}
