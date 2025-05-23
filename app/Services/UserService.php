<?php

namespace App\Services;

use App\Models\User;

class UserService
{

    public function updateUser(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->save();
        return $user;
    }
}
