<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Board;
use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserRoleService
{
    private RoleRepository $roleRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(RoleRepository $roleRepository, EntityManagerInterface $entityManager)
    {
        $this->roleRepository = $roleRepository;
        $this->entityManager = $entityManager;
    }

    public function linkBoardToUser(Board $board, User $currrentUser): void
    {
        $userRole = new UserRole();
        $userRole->setBoard($board);
        $userRole->setUser($currrentUser);
        $role = $this->roleRepository->findOneBy(['name' => 'admin']);
        if (!$role) {
            throw new \Exception('Role "admin" not found');
        }
        $userRole->setRole($role);

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($userRole);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException('Could not link user to board: '.$e->getMessage());
        }
    }
}
