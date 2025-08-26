<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryMen extends Model
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    /**
     * Route notifications for the broadcast channel.
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'deliverymen.' . $this->id;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the unread notifications for the delivery person.
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}
