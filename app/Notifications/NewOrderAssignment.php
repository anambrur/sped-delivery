<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewOrderAssignment extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        // Use database and broadcast channels
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'restaurant_name' => $this->order->restaurant->name,
            'customer_name' => $this->order->customer_name,
            'delivery_address' => $this->order->delivery_address,
            'total_amount' => $this->order->total_amount,
            'created_at' => $this->order->created_at->toDateTimeString(),
            'message' => 'New order #' . $this->order->id . ' has been assigned to you',
            'type' => 'new_order_assignment'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'new_order_assignment',
            'data' => [
                'order_id' => $this->order->id,
                'restaurant_name' => $this->order->restaurant->name,
                'customer_name' => $this->order->customer_name,
                'delivery_address' => $this->order->delivery_address,
                'total_amount' => $this->order->total_amount,
                'created_at' => $this->order->created_at->toDateTimeString(),
                'message' => 'New order #' . $this->order->id . ' has been assigned to you'
            ],
            'read_at' => null,
            'created_at' => now()->toDateTimeString()
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'restaurant_name' => $this->order->restaurant->name,
            'customer_name' => $this->order->customer_name,
            'delivery_address' => $this->order->delivery_address,
            'total_amount' => $this->order->total_amount,
            'message' => 'New order assigned to you!'
        ];
    }
}
