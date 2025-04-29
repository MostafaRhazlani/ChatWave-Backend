<?php

namespace App\Jobs;

use App\Events\UserStatusChanged;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BroadcastUserStatusChanged implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $isLoggedIn;

    public function __construct($userId, $isLoggedIn)
    {
        $this->userId = $userId;
        $this->isLoggedIn = $isLoggedIn;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new UserStatusChanged($this->userId, $this->isLoggedIn));
    }
}
