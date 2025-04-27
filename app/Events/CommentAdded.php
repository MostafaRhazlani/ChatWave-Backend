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

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $notification;
    protected $postAuthorId;
    /**
     * Create a new event instance.
     */
    public function __construct($notification, $postAuthorId)
    {
        $this->notification = $notification;
        $this->postAuthorId = $postAuthorId;
        Log::info('Broadcasting to: comment-post.' . $this->postAuthorId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('comment-post.' . $this->postAuthorId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'comment.added';
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
