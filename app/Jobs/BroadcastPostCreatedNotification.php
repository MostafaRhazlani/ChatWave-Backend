<?php

namespace App\Jobs;

use App\Events\PostCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BroadcastPostCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;
    protected $receiver;
    /**
     * Create a new job instance.
     */
    public function __construct($notification, $receiver)
    {
        $this->notification = $notification;
        $this->receiver = $receiver;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new PostCreated($this->notification, $this->receiver))->toOthers();
    }
}
