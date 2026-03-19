<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePhoto extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'ordem', 'principal'];

    protected $casts = [
        'principal' => 'boolean',
        'ordem'     => 'integer',
    ];

    protected $appends = ['url', 'thumb_url'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbUrlAttribute(): string
    {
        $thumbPath = str_replace(basename($this->path), 'thumbs/' . basename($this->path), $this->path);

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($thumbPath)) {
            return asset('storage/' . $thumbPath);
        }

        return $this->url;
    }
}
