<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\BaseRequest;

class StoreChatRequest extends BaseRequest
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
            'is_group_chat' => 'required|boolean',
            'name' => 'required_if:is_group_chat,true|string',
            'users' => 'required|array'
        ];
    }
}
