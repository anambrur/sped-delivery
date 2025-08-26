<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];


    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
