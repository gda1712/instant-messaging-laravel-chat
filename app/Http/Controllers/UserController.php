<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Controllers\BaseController;

class UserController extends BaseController
{
    public function show(Request $request)
    {
        return $request->user();
    }

    public function update(UpdateUserRequest $request)
    {
        $validated = $request->validated();
        $name = $validated['name'] ?? null;
        $theme = $validated['theme'] ?? null;

        try {
            $user = User::where('id', auth()->user()->id)->first();
            if ($name) {
                $user->name = $name;
            }
            if ($theme) {
                $user->theme = $theme;
            }
            $user->save();
            return $this->sendResponse($user, 'User updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }

    }
}
