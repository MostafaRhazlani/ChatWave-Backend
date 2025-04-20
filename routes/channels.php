<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
    logger("User trying to join channel: user_id={$user->id}, receiver_id={$receiver_id}");
    return (int) $user->id === (int) $receiver_id;
});
