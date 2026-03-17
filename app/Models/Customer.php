<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'cpf', 'telefone', 'email',
        'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado',
        'observacoes',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class)->latest();
    }

    public function getEnderecoCompletoAttribute(): string
    {
        $parts = array_filter([
            $this->endereco,
            $this->numero,
            $this->bairro,
            $this->cidade,
            $this->estado,
        ]);

        return implode(', ', $parts);
    }
}
