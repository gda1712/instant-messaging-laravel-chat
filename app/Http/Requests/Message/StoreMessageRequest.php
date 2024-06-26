<?php

namespace App\Http\Requests\Message;

use App\Http\Requests\BaseRequest;

class StoreMessageRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'nullable|file|max:10240',
            'message' => 'required_unless:file,null|string',
            'chat_id' => 'required|integer|exists:chats,id',
        ];
    }
}
