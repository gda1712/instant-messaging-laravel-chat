<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Models\Group;
use App\Models\User;

class GroupController extends BaseController
{
    //
    public function store(StoreGroupRequest $request)
    {
        $validated = $request->validated();
        try {
            $group = Group::create($validated);
            $users = User::whereIn('email', $validated['users'])->get()->pluck('id');
            $group->users()->attach($users);
            return $this->sendResponse($group, 'Group created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $groups = Group::whereIn('id', function($query) {
                $query->select('group_id')->from('group_user')->where('user_id', auth()->user()->id);
            })->get();
            return $this->sendResponse($groups, 'Groups retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // TODO: validate who can delete this group
            $group = Group::findOrFail($id);
            $group->delete();
            return $this->sendResponse([], 'Group deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }
}
