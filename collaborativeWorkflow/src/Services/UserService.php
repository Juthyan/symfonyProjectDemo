<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserService
{
    private UserRepository $userRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function fetchUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function fetchUserByUsername(string $userName): ?User
    {
        return $this->userRepository->findOneBy(['userName' => $userName]);
    }

    public function createUser(UserDto $userDto): JsonResponse
    {
        $user = new User();
        $user->setUsername($userDto->getUserName());
        $user->setEmail($userDto->getMail());

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            return new JsonResponse(['status' => 'Creation failed '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'User created'], 201);
    }
}
