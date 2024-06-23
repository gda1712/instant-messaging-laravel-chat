<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Models\Message;
use App\Events\NewMessage;

class MessageController extends BaseController
{
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        try {
            $message = Message::create($validated);
            $message->load('user');
            broadcast(new NewMessage($message))->toOthers();
            return $this->sendResponse($message, 'Message created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }
}
