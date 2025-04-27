<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $notification;
    protected $receiver;
    /**
     * Create a new event instance.
     */
    public function __construct($notification, $receiver)
    {
        $this->notification = $notification;
        $this->receiver = $receiver;
        Log::info('Broadcasting to: post-created.' . $this->receiver);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('post-created.' . $this->receiver),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'post.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            "id" => $this->notification->id,
            "type" => $this->notification->type,
            "content" => $this->notification->content,
            "is_read" => $this->notification->is_read,
            "receiver_id" => $this->notification->receiver_id,
            "sender_id" => $this->notification->sender_id,
            "created_at" => $this->notification->created_at,
            "sender" => [
                "id" => $this->notification->sender->id,
                "full_name" => $this->notification->sender->full_name,
                "image" => $this->notification->sender->image
            ]
        ];
    }
}
