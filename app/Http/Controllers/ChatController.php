<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function getMessages(User $user): AnonymousResourceCollection
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        // Mark all unread messages from this specific user as read
        $authUser->unreadMessages()
            ->where('sender_id', $user->id)
            ->update(['read_at' => now()]);

        $messages = $authUser
            ->allMessagesWith($user)
            ->oldest()
            ->get();

        return MessageResource::collection($messages);
    }

    public function getUnreadMessages(): AnonymousResourceCollection
    {
        $messages = auth()->user()->unreadMessages()
            ->with('sender')
            ->latest()
            ->get();

        return MessageResource::collection($messages);
    }

    public function sendMessage(StoreMessageRequest $request): MessageResource
    {
        $message = auth()->user()->sentMessages()->create([
            'receiver_id' => $request->receiver_id,
            'text' => $request->text,
        ]);

        // Eager load sender so the resource has it immediately
        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return new MessageResource($message);
    }
}
