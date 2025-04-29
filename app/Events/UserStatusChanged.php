<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $userId;
    protected $isLoggedIn;

    public function __construct($userId, $isLoggedIn)
    {
        $this->userId = $userId;
        $this->isLoggedIn = $isLoggedIn;
    }

    public function broadcastOn()
    {
        return new Channel('user-status');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.status';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            "user_status" => [
                "userId" => $this->userId,
                "isLoggedIn" => $this->isLoggedIn,
            ]
        ];
    }
}

