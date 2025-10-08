<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserRoleRepository $userRoleRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, UserRoleRepository $userRoleRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userRoleRepository = $userRoleRepository;
        $this->passwordHasher = $passwordHasher;
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
        $user->setMail($userDto->getMail());

        $hashedPassword = $this->passwordHasher->hashPassword($user, $userDto->getPassword());
        $user->setPassword($hashedPassword);

        try {
            $this->saveUser($user);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Creation failed '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'User created'], 201);
    }

    public function editUser(int $id, UserDto $dto): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(['status' => 'User not found'], 404);
        }

        $user->setUserName($dto->getUserName());
        $user->setMail($dto->getMail());

        if ($dto->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->getPassword());
            $user->setPassword($hashedPassword);
        }

        if (!empty($dto->userRoleIds)) {
            $userRoleIds = $dto->userRoleIds;
            $userRoles = $this->userRoleRepository->findBy(['id' => $userRoleIds]);
            $user->setUserRoles(new ArrayCollection($userRoles));
        }

        try {
            $this->saveUser($user);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Update failed: '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'User updated'], 200);
    }

    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(['status' => 'User not found'], 404);
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'Delete user failed '.$e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'User deleted'], 204);
    }

    private function saveUser(User $user): void
    {
        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
