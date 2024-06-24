<?php

namespace App\Http\Controllers;

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

        try {
            $user = $request->user();
            $user->update($request->validated());
            $user->refresh();
            return $this->sendResponse($user, 'User updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }

    }
}
