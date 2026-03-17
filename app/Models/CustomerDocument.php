<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomerDocument extends Model
{
    protected $fillable = [
        'customer_id', 'name', 'original_name', 'path', 'mime_type', 'size', 'categoria',
    ];

    public const CATEGORIAS = [
        'identidade'    => 'Identidade / RG',
        'cpf'           => 'CPF',
        'cnh'           => 'CNH',
        'comprovante'   => 'Comprovante de residência',
        'contrato'      => 'Contrato',
        'recibo'        => 'Recibo',
        'procuracao'    => 'Procuração',
        'outros'        => 'Outros',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getCategoriaLabelAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? $this->categoria;
    }

    public function getSizeFormatadoAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        return number_format($bytes / 1024, 0, ',', '.') . ' KB';
    }

    public function getIconClassAttribute(): string
    {
        $mime = $this->mime_type;

        if (str_contains($mime, 'pdf')) return 'fas fa-file-pdf text-danger';
        if (str_contains($mime, 'image')) return 'fas fa-file-image text-primary';
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) return 'fas fa-file-word text-info';
        if (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) return 'fas fa-file-excel text-success';
        if (str_contains($mime, 'zip') || str_contains($mime, 'rar')) return 'fas fa-file-archive text-warning';

        return 'fas fa-file text-secondary';
    }

    protected static function booted()
    {
        static::deleting(function (CustomerDocument $doc) {
            Storage::disk('local')->delete($doc->path);
        });
    }
}
