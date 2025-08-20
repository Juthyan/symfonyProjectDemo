<?php

namespace App\Formatter;

use App\Entity\User;

class UserFormatter
{
    public function formatUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUserName(),
            'role' => $user->getUserRoles()->toArray(),
        ];
    }
}
