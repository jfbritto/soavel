<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition()
    {
        return [
            'descricao' => $this->faker->sentence(3),
            'valor' => $this->faker->randomFloat(2, 50, 5000),
            'categoria' => 'manutencao',
            'vehicle_id' => null,
            'data' => now(),
            'user_id' => User::factory(),
        ];
    }
}
