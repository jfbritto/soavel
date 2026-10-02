<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    public const TIPOS_PESSOA = [
        'pf' => 'Pessoa Física',
        'pj' => 'Pessoa Jurídica',
    ];

    protected $fillable = [
        'nome', 'tipo_pessoa', 'cpf', 'cnpj', 'telefone', 'email',
        'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado',
        'observacoes',
    ];

    protected $attributes = [
        'tipo_pessoa' => 'pf',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class)->latest();
    }

    public function getIsPjAttribute(): bool
    {
        return $this->tipo_pessoa === 'pj';
    }

    public function getTipoPessoaLabelAttribute(): string
    {
        return self::TIPOS_PESSOA[$this->tipo_pessoa] ?? $this->tipo_pessoa;
    }

    /**
     * CNPJ para pessoa jurídica, CPF para pessoa física.
     */
    public function getDocumentoAttribute(): ?string
    {
        return $this->is_pj ? $this->cnpj : $this->cpf;
    }

    public function getDocumentoLabelAttribute(): string
    {
        return $this->is_pj ? 'CNPJ' : 'CPF';
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
