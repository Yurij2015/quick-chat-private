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
        $messages = auth()->user()
            ->allMessagesWith($user)
            ->oldest()
            ->get();

        return MessageResource::collection($messages);
    }

    public function sendMessage(StoreMessageRequest $request): MessageResource
    {
        $message = auth()->user()->sentMessages()->create([
            'receiver_id' => $request->receiver_id,
            'text' => $request->text,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return new MessageResource($message);
    }
}
