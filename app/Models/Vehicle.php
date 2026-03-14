<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::deleting(function (Vehicle $vehicle) {
            // Remove arquivos físicos das fotos
            foreach ($vehicle->photos as $photo) {
                Storage::disk('public')->delete($photo->path);
            }
            // Remove arquivos físicos dos documentos (dispara o deleting de cada um)
            $vehicle->documents->each->delete();
        });
    }

    protected $fillable = [
        'marca', 'modelo', 'versao', 'ano_fabricacao', 'ano_modelo',
        'km', 'preco', 'preco_compra', 'cor', 'combustivel', 'transmissao',
        'portas', 'motorizacao', 'categoria', 'status', 'descricao',
        'destaque', 'slug', 'placa', 'renavam', 'chassi',
    ];

    protected $casts = [
        'preco'        => 'decimal:2',
        'preco_compra' => 'decimal:2',
        'destaque'     => 'boolean',
        'km'           => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDisponivel($query)
    {
        return $query->where('status', 'disponivel');
    }

    public function scopeDestaque($query)
    {
        return $query->where('destaque', true);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('ordem');
    }

    public function principalPhoto()
    {
        return $this->hasOne(VehiclePhoto::class)->where('principal', true);
    }

    public function features()
    {
        return $this->hasMany(VehicleFeature::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class)->latest();
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'vehicle_partners')
                    ->withPivot('percentual')
                    ->withTimestamps();
    }

    /**
     * Venda na qual este veículo foi entrado como troca.
     */
    public function vendaOrigem()
    {
        return $this->hasOne(Sale::class, 'troca_vehicle_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTituloAttribute(): string
    {
        return trim("{$this->marca} {$this->modelo} " . ($this->versao ?? ''));
    }

    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco, 0, ',', '.');
    }

    public function getKmFormatadoAttribute(): string
    {
        return number_format($this->km, 0, ',', '.') . ' km';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disponivel' => 'Disponível',
            'reservado'  => 'Reservado',
            'vendido'    => 'Vendido',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'disponivel' => 'success',
            'reservado'  => 'warning',
            'vendido'    => 'danger',
            default      => 'secondary',
        };
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function generateSlug(string $marca, string $modelo, int $ano): string
    {
        $base = Str::slug("{$marca} {$modelo} {$ano}");
        $slug = $base;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
