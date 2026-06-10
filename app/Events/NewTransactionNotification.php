<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTransactionNotification
implements ShouldBroadcast
{
    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;

    public $notification;

    public function __construct(
        $notification
    ) {
        $this->notification =
            $notification;
    }

    public function broadcastOn()
    {
        return new Channel(
            'pharmacy-notifications'
        );
    }

    public function broadcastAs()
    {
        return 'new.notification';
    }

    public function broadcastWith()
    {
        return [

            'id' =>
                $this->notification->id,

            'title' =>
                $this->notification->title,

            'message' =>
                $this->notification->message,

            'type' =>
                $this->notification->type,

            'is_read' =>
                $this->notification->is_read,

            'created_at' =>
                $this->notification
                    ->created_at
                    ->format(
                        'Y-m-d H:i:s'
                    )
        ];
    }
}