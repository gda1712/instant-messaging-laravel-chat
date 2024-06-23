<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\chat\StorechatRequest;
use App\Models\Chat;
use App\Models\User;
use App\Events\ChatCreated;

class ChatController extends BaseController
{
    //
    public function store(StorechatRequest $request)
    {
        $validated = $request->validated();
        try {
            $chat = Chat::create([
                'name' => $validated['name']
            ]);
            $users = User::whereIn('email', $validated['users'])->get()->pluck('id');
            $chat->users()->attach($users);
            broadcast(new ChatCreated($chat))->toOthers();
            return $this->sendResponse($chat, 'chat created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $chats = Chat::whereIn('id', function($query) {
                $query->select('chat_id')->from('chat_user')->where('user_id', auth()->user()->id);
            })->get();
            return $this->sendResponse($chats, 'chats retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // TODO: validate who can delete this chat
            $chat = Chat::findOrFail($id);
            $chat->delete();
            return $this->sendResponse([], 'chat deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }
}
