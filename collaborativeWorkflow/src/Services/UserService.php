<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function fetchUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function fetchUserByUsername(string $userName): User
    {
        return $this->userRepository->findOneBy(['userName' => $userName]);
    }
}
