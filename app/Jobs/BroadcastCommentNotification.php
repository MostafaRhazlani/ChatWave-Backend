<?php

namespace App\Jobs;

use App\Events\CommentAdded;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BroadcastCommentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notification;
    public $postAuthorId;

    /**
     * Create a new job instance.
     */
    public function __construct($notification, $postAuthorId)
    {
        $this->notification = $notification;
        $this->postAuthorId = $postAuthorId;
        Log::info('Broadcasting from jobs to: comment-post.' . $this->postAuthorId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new CommentAdded($this->notification, $this->postAuthorId));
    }
}
