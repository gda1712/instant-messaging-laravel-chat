<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use App\Http\Requests\chat\StoreChatRequest;
use App\Http\Requests\Chat\updateChatStatusRequest;
use App\Models\Chat;
use App\Models\User;
use App\Events\ChatCreated;

class ChatController extends BaseController
{
    //
    public function store(StoreChatRequest $request)
    {
        $validated = $request->validated();
        $isGroupChat = $validated['is_group_chat'];
        $name = $isGroupChat ? $validated['name'] : null;
        $users = $validated['users'];

        if(count($users) !== 1 && !$isGroupChat) {
            return $this->sendError('Error', ['error' => 'Only one user can be in a chat']);
        }

        try {
            $users = User::whereIn('email', $validated['users'])->get()->pluck('id');

            // check if there is a chat with the same users before
            if(!$isGroupChat) {
                $userToCheck = $users[0];

                $existsChatBefore = $chats = Chat::where('is_group_chat', 0)
                    ->whereIn('id', function($query) use($userToCheck) {
                        $query->select('cu.chat_id')
                            ->from('chat_user as cu')
                            ->join('chat_user as cu2', 'cu.chat_id', '=', 'cu2.chat_id')
                            ->where('cu.user_id', auth()->user()->id)
                            ->where('cu2.user_id', $userToCheck);
                    })
                    ->exists();

                if($existsChatBefore) {
                    return $this->sendError('Error', ['error' => 'Chat already exists'], 400);
                }
            }

            $chat = Chat::create([
                'name' => $name,
                'is_group_chat' => $isGroupChat,
            ]);
            $users[] = auth()->user()->id;

            $chat->users()->attach($users);
            $chat->refresh();
            $chat->load('users');
            if ($chat->is_group_chat == 0) {
                // Get user that is not the authenticated user
                $otherUser = $chat->users->first(function ($user) {
                    return $user->id !== auth()->user()->id;
                });

                // Set the chat name to the other user's name
                if ($otherUser) {
                    $chat->name = $otherUser->name;
                }
            }
            broadcast(new ChatCreated($chat));
            return $this->sendResponse($chat, 'chat created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $chats = Chat::whereIn('chats.id', function($query) {
                $query->select('chat_id')->from('chat_user')->where('user_id', auth()->user()->id);
            })->join('chat_user as cu', 'chats.id', '=', 'cu.chat_id')
                ->where('cu.user_id', auth()->user()->id)
                ->select('chats.id', 'chats.name', 'chats.is_group_chat')
                ->get()
                ->load('users');

            $chats->each(function ($chat) {
                if ($chat->is_group_chat == 0) {
                    // Get user that is not the authenticated user
                    $otherUser = $chat->users->first(function ($user) {
                        return $user->id !== auth()->user()->id;
                    });

                    // Set the chat name to the other user's name
                    if ($otherUser) {
                        $chat->name = $otherUser->name;
                    }
                }
            });
            return $this->sendResponse($chats, 'chats retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function getContacts(Request $request)
    {
        try {
            $contacts = User::whereIn('id', function($query) {
                $query->select('user_id')->from('chat_user')->whereIn('chat_id', function($query) {
                    $query->select('chat_id')->from('chat_user')->where('user_id', auth()->user()->id);
                });
            })->where('id', '!=', auth()->user()->id)->select('id', 'name', 'email')->get();

            return $this->sendResponse($contacts, 'contacts retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id) {
        /* remove user in the chat if chat_type is group chat, if not remove the chat */
        try {
            $chat = Chat::find($id);
            if (!$chat) {
                return $this->sendError('Error', ['error' => 'Chat not found'], 404);
            }

            if ($chat->is_group_chat) {
                $chat->users()->detach(auth()->user()->id);
                // add message that user left the chat
                Message::create([
                    'chat_id' => $chat->id,
                    'user_id' => auth()->user()->id,
                    'message' => null,
                    'type' => Message::TYPE_REMOVED_CHAT
                ]);
            } else {
                $chat->delete();
            }

            return $this->sendResponse([], 'Chat removed successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }
}
