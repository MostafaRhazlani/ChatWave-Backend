<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Message;
use App\Events\MessageSent;
use App\Jobs\SendChatMessage;
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

    public function getConversation(Request $request, $friend_id) {
        $userId = $request->user()->id;

        $friend = Person::where('id', $friend_id)->select('id', 'full_name', 'image')->first();
        $messages = Message::where(function($query) use ($userId, $friend_id) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $friend_id);
        })->orWhere(function($query) use ($userId, $friend_id) {
            $query->where('receiver_id', $userId)
                  ->where('sender_id', $friend_id);
        })->with(['sender', 'receiver'])->orderBy('created_at')->get();

        return response()->json(['messages' => $messages, 'friend' => $friend]);
    }

    public function sendMessage(Request $request) {

        $userId = $request->user()->id;
        $validated = $request->validate([
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'messageType' => 'nullable|in:image,video,document'
        ]);

        $message = new Message();
        $message->sender_id = $userId;
        $message->receiver_id = $request->receiver_id;
        if($request->content) {
            $message->content = $request->content;
        }
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $fileExtension = pathinfo($media->getClientOriginalName(), PATHINFO_EXTENSION);
            $fileType = strtolower($fileExtension);
            if($validated['messageType'] === 'video') {
                if(in_array($fileType, ['mp4'])) {
                    $uniqueName = uniqid() . '.' . $media->getClientOriginalExtension();
                    $media->storeAs('chat/videos', $uniqueName, 'public');
                    $message->media = $uniqueName;
                } else {
                    return response()->json(['error' => 'Invalid video file'], 400);
                }
            } else if($validated['messageType'] === 'image') {
                if(in_array($fileType, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $uniqueName = uniqid() . '.' . $media->getClientOriginalExtension();
                    $media->storeAs('chat/images', $uniqueName, 'public');
                    $message->media = $uniqueName;
                } else {
                    return response()->json(['error' => 'Invalid image file'], 400);
                }
            } else if($validated['messageType'] === 'document') {
                if(in_array($fileType, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'])) {
                    $uniqueName = uniqid() . '.' . $media->getClientOriginalExtension();
                    $media->storeAs('chat/documents', $uniqueName, 'public');
                    $message->media = $uniqueName;
                } else {
                    return response()->json(['error' => 'Invalid document file'], 400);
                }
            }
            $message->messageType = $validated['messageType'];
        }
        $message->save();

        dispatch(new SendChatMessage($message));

        return response()->json(['message' => $message]);
    }

    public function edit($id) {

        $message = Message::find($id);
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, $id) {

        $validated = $request->validate([
            'content' => 'required',
        ]);

        $message = Message::find($id);
        $message->update([
            'content' => $validated['content'],
        ]);

        return response()->json(['message' => $message]);
    }
}
