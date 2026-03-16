<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name(),
            'cpf' => $this->faker->unique()->numerify('###.###.###-##'),
            'telefone' => '(28) 99999-' . $this->faker->numerify('####'),
            'email' => $this->faker->unique()->safeEmail(),
            'cep' => $this->faker->numerify('#####-###'),
            'endereco' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'bairro' => 'Centro',
            'cidade' => $this->faker->city(),
            'estado' => 'ES',
        ];
    }
}
