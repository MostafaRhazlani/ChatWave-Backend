<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function contacts(Request $request) {
        $authUser = $request->user();

        try {
            $sendTo = $authUser->sentMessage()->pluck('receiver_id')->toArray();
            $recieverFrom = $authUser->receivedMessage()->pluck('sender_id')->toArray();

            $contactsIds = array_unique(array_merge($sendTo, $recieverFrom));
            $contacts = Person::whereIn('id', $contactsIds)->get();

            $lastMessageEachUser = $contacts->map(function ($contact) use ($authUser) {
                $lastMessage = Message::where(function ($query) use ($authUser, $contact) {
                    $query->where('sender_id', $authUser->id)
                          ->where('receiver_id', $contact->id);
                })->orWhere(function ($query) use ($authUser, $contact) {
                    $query->where('sender_id', $contact->id)
                          ->where('receiver_id', $authUser->id);
                })->orderBy('created_at', 'DESC')->first();

                return [
                    'id' => $contact->id,
                    'username' => $contact->username,
                    'full_name' => $contact->full_name,
                    'image' => $contact->image,
                    'lastMessage' => $lastMessage
                ];
            });

            return response()->json(['contacts' => $lastMessageEachUser], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
