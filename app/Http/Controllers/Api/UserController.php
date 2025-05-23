<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserEditRequest;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function edit(UserEditRequest $request)
    {
        $user = auth()->user();

        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'message' => 'User profile updated successfully',
            'user' => new UserResource($updatedUser),
        ]);
    }
    public function index()
    {
        $users = User::paginate(10);

        return UserResource::collection($users);
    }
}
