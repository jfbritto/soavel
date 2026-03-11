<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'customer_id', 'user_id',
        'troca_vehicle_id', 'valor_troca',
        'preco_venda', 'tipo_pagamento', 'financiadora',
        'parcelas', 'entrada', 'data_venda', 'status', 'observacoes',
    ];

    protected $casts = [
        'preco_venda'  => 'decimal:2',
        'entrada'      => 'decimal:2',
        'valor_troca'  => 'decimal:2',
        'data_venda'   => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trocaVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'troca_vehicle_id');
    }

    public function getTipoPagamentoLabelAttribute(): string
    {
        return match ($this->tipo_pagamento) {
            'a_vista'    => 'À Vista',
            'financiado' => 'Financiado',
            'consorcio'  => 'Consórcio',
            'permuta'    => 'Permuta',
            'misto'      => 'Misto',
            default      => $this->tipo_pagamento,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendente'  => 'Pendente',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendente'  => 'warning',
            'concluida' => 'success',
            'cancelada' => 'danger',
            default     => 'secondary',
        };
    }

    public function getPrecoVendaFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_venda, 0, ',', '.');
    }
}
