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
            'tipo_pessoa' => 'pf',
            'cpf' => $this->faker->unique()->numerify('###.###.###-##'),
            'cnpj' => null,
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

    public function pj()
    {
        return $this->state([
            'nome'        => $this->faker->company(),
            'tipo_pessoa' => 'pj',
            'cpf'         => null,
            'cnpj'        => $this->faker->unique()->numerify('##.###.###/####-##'),
        ]);
    }
}
