<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserDto;
use App\Formatter\UserFormatter;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
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

    #[Route('/save', name: 'create_user', methods: ['POST'])]
    public function createUser(UserPasswordHasherInterface $passwordHasher, #[MapRequestPayload(validationGroups: ['create'])] UserDto $user): Response
    {
        return $this->userService->createUser($user);
    }

    #[Route('/edit/{id}', name: 'edit_user', methods: ['PATCH'])]
    public function editUser(int $id, #[MapRequestPayload(validationGroups: ['edit'])] UserDto $dto): JsonResponse
    {
        return $this->userService->editUser($id, $dto);
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        return $this->userService->deleteUser($id);
    }
}
