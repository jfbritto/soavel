<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'cpf', 'telefone', 'email', 'observacoes'];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_partners')
                    ->withPivot('percentual')
                    ->withTimestamps();
    }
}
