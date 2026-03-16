<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition()
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'preco_venda' => $this->faker->randomFloat(2, 30000, 200000),
            'tipo_pagamento' => 'a_vista',
            'data_venda' => now(),
            'status' => 'concluida',
        ];
    }

    public function pendente()
    {
        return $this->state(['status' => 'pendente']);
    }

    public function cancelada()
    {
        return $this->state(['status' => 'cancelada']);
    }
}
