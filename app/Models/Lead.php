<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'telefone', 'email', 'vehicle_id',
        'interesse', 'origem', 'status', 'observacoes', 'user_id',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name' => '—']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'novo'        => 'Novo',
            'em_contato'  => 'Em Contato',
            'convertido'  => 'Convertido',
            'perdido'     => 'Perdido',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'novo'       => 'primary',
            'em_contato' => 'info',
            'convertido' => 'success',
            'perdido'    => 'danger',
            default      => 'secondary',
        };
    }

    public function getOrigemLabelAttribute(): string
    {
        return match ($this->origem) {
            'site'        => 'Site',
            'whatsapp'    => 'WhatsApp',
            'presencial'  => 'Presencial',
            'indicacao'   => 'Indicação',
            'instagram'   => 'Instagram',
            'outro'       => 'Outro',
            default       => $this->origem,
        };
    }
}
