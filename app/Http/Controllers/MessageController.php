<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Requests\Message\IndexMessagesRequest;
use App\Models\Message;
use App\Events\NewMessage;
use App\Services\EncryptionService;

class MessageController extends BaseController
{
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->user()->id;
        $encryptionService = new EncryptionService(auth()->user()->email);

        try {
            // verify that the user is part of the chat
            $chat = Chat::where('id', $validated['chat_id'])->whereHas('users', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })->first();
            if (!$chat) {
                return $this->sendError('Error', ['error' => 'You are not part of this chat'], 403);
            }

            // Decrypt message
            $validated['message'] = $encryptionService->cryptoJsAesDecrypt($validated['message']);

            // save file in 'chat' disk
            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $timestampedFilename = time() . $uploadedFile->getClientOriginalName();
                $subfolder = 'chat/' . $validated['chat_id'];
                $filePath = Storage::disk('chat')->putFileAs(
                    $subfolder,
                    $uploadedFile,
                    $timestampedFilename
                );
                $validated['file'] = $filePath;
            }

            $message = Message::create($validated);
            broadcast(new NewMessage($message))->toOthers();
            $message->message = $encryptionService->cryptoJsAesEncrypt($message->message);
            return $this->sendResponse($message, 'Message created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function index(IndexMessagesRequest $request)
    {
        $validated = $request->validated();
        $chatId = $validated['chat_id'] ?? null;
        $search = $validated['search'] ?? null;
        $encryptionService = new EncryptionService(auth()->user()->email);
        $user = auth()->user();

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

            if($search) {
                $query->where('message', 'like', "%$search%");
            }

            $appUrl = env('APP_URL');

            $query->select(
                'id',
                'chat_id',
                'user_id',
                'message',
                'type',
                'created_at',
                DB::raw("IF(file IS NOT NULL, CONCAT('$appUrl/api/chat/file/', id), NULL) as file_url")
            );

            $messages = $query->with('user')->get();

            $messages->each(function ($message) use ($encryptionService) {
                $message->message = $encryptionService->cryptoJsAesEncrypt($message->message);
            });


            return $this->sendResponse($messages, 'Messages retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getTrace()], 500);
        }
    }

    // Download file in message
    public function downloadFile(Request $request, $id)
    {
        $message = Message::find($id);

        if (!$message) {
            return $this->sendError('Error', ['error' => 'Message not found'], 404);
        }

        $message->load('chat.users');

        // check if the user is part of the chat
        $isPartOfChat = $message->chat->users->contains('id', auth()->user()->id);
        if (!$isPartOfChat) {
            return $this->sendError('Error', ['error' => 'You are not part of this chat'], 403);
        }

        if (!$message->file) {
            return $this->sendError('Error', ['error' => 'Message does not have a file'], 404);
        }

        $path = Storage::disk('chat')->path($message->file);
        return response()->download($path);
    }
}
