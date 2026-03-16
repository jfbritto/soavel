<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name(),
            'telefone' => '(28) 99999-' . $this->faker->numerify('####'),
            'email' => $this->faker->safeEmail(),
            'vehicle_id' => null,
            'interesse' => 'Interesse em veículo',
            'origem' => 'site',
            'status' => 'novo',
            'observacoes' => null,
            'user_id' => null,
        ];
    }

    public function convertido()
    {
        return $this->state(['status' => 'convertido']);
    }

    public function perdido()
    {
        return $this->state(['status' => 'perdido']);
    }
}
