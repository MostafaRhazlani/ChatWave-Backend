<?php

namespace App\Jobs;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendChatMessage implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('Broadcasting from jobs to: chat.' . $this->message->receiver_id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new MessageSent($this->message));
    }
}
