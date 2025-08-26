<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    protected $casts = [
        'order_items' => 'array',
    ];

    /**
     * Get the restaurant that owns the order.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the delivery person assigned to the order.
     */
    public function deliveryPerson()
    {
        return $this->belongsTo(DeliveryMen::class, 'delivery_men_id');
    }
}
