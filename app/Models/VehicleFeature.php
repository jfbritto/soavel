<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleFeature extends Model
{
    public $timestamps = false;

    protected $fillable = ['vehicle_id', 'feature'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
