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

    protected $appends = ['url'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
