<?php

declare(strict_types=1);

namespace App\Controller;

use App\Formatter\UserFormatter;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    private UserService $userService;
    private UserFormatter $userFormatter;

    public function __construct(UserService $userService, UserFormatter $userFormatter)
    {
        $this->userService = $userService;
        $this->userFormatter = $userFormatter;
    }

    #[Route('', name: 'app_user')]
    public function fetchAllUsers(): Response
    {
        $users = $this->userService->fetchUsers();
        $data = [];
        foreach ($users as $user) {
            $data[] = $this->userFormatter->formatUser($user);
        }

        return new JsonResponse($data);
    }

    #[Route('/{username}', name: 'get_user', methods: ['GET'])]
    public function fetchUser(string $username): Response
    {
        $user = $this->userService->fetchUserByUsername($username);

        return new JsonResponse($this->userFormatter->formatUser($user));
    }
}
