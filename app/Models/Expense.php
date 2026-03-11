<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao', 'valor', 'categoria', 'vehicle_id', 'data', 'user_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data'  => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->withDefault(['titulo' => '—']);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name' => '—']);
    }

    public function getCategoriaLabelAttribute(): string
    {
        return match ($this->categoria) {
            'manutencao'   => 'Manutenção',
            'documentacao' => 'Documentação',
            'limpeza'      => 'Limpeza',
            'combustivel'  => 'Combustível',
            'comissao'     => 'Comissão',
            'outros'       => 'Outros',
            default        => $this->categoria,
        };
    }

    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
}
