<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\UserService;

final class RegistrationController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        UserPasswordHasherInterface $passwordHasher,
        #[MapRequestPayload] UserDto $userDto
    ): JsonResponse {
        return $this->userService->createUser($userDto, $passwordHasher);
    }

}
