<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Requests\Message\IndexMessagesRequest;
use App\Models\Message;
use App\Events\NewMessage;

class MessageController extends BaseController
{
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->user()->id;
        try {
            // verify that the user is part of the chat
            $chat = Chat::where('id', $validated['chat_id'])->whereHas('users', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })->first();
            if (!$chat) {
                return $this->sendError('Error', ['error' => 'You are not part of this chat'], 403);
            }

            $message = Message::create($validated);
            broadcast(new NewMessage($message))->toOthers();
            return $this->sendResponse($message, 'Message created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function index(IndexMessagesRequest $request)
    {
        $validated = $request->validated();
        $chatId = $validated['chat_id'] ?? null;
        try {
            if($chatId) {
                // verify that the user is part of the chat
                $chat =  Chat::where('id', $chatId)->whereHas('users', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })->first();
                if (!$chat) {
                    return $this->sendError('Error', ['error' => 'You are not part of this chat'], 403);
                }
            }

            $query = Message::query();
            if($chatId) {
                $query->where('chat_id', $chatId);
            } else {
                // Todo: test this
                $query->whereHas('chat', function ($query) {
                    $query->whereHas('users', function ($query) {
                        $query->where('user_id', auth()->user()->id);
                    });
                });
            }
            $messages = $query->with('user')->get();
            return $this->sendResponse($messages, 'Messages retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }
}
