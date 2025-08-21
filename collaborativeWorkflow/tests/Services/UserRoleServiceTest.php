<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Board;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\RoleRepository;
use App\Services\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UserRoleServiceTest extends MockeryTestCase
{
    private $roleRepositoryMock;
    private $entityManagerMock;
    private UserRoleService $service;

    protected function setUp(): void
    {
        $this->roleRepositoryMock = \Mockery::mock(RoleRepository::class);
        $this->entityManagerMock = \Mockery::mock(EntityManagerInterface::class);

        $this->service = new UserRoleService($this->roleRepositoryMock, $this->entityManagerMock);
    }

    public function testLinkBoardToUserSuccess(): void
    {
        $board = \Mockery::mock(Board::class);
        $user = \Mockery::mock(User::class);
        $role = \Mockery::mock(Role::class);

        // Role repository returns a role
        $this->roleRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['name' => 'admin'])
            ->andReturn($role);

        // EntityManager transaction methods called once
        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(\Mockery::type(UserRole::class));
        $this->entityManagerMock->expects('flush')->once();
        $this->entityManagerMock->expects('commit')->once();
        $this->entityManagerMock->expects('rollback')->never();

        // Call the method
        $this->service->linkBoardToUser($board, $user);

        // If no exceptions, test passes
        $this->assertTrue(true);
    }

    public function testLinkBoardToUserThrowsExceptionWhenRoleNotFound(): void
    {
        $board = \Mockery::mock(Board::class);
        $user = \Mockery::mock(User::class);

        $this->roleRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['name' => 'admin'])
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Role "admin" not found');

        $this->service->linkBoardToUser($board, $user);
    }

    public function testLinkBoardToUserRollsBackOnException(): void
    {
        $board = \Mockery::mock(Board::class);
        $user = \Mockery::mock(User::class);
        $role = \Mockery::mock(Role::class);

        $this->roleRepositoryMock
            ->expects('findOneBy')
            ->once()
            ->with(['name' => 'admin'])
            ->andReturn($role);

        $this->entityManagerMock->expects('beginTransaction')->once();
        $this->entityManagerMock->expects('persist')->once()->with(\Mockery::type(UserRole::class));
        $this->entityManagerMock->expects('flush')->andThrow(new \Exception('DB Error'));
        $this->entityManagerMock->expects('rollback')->once();
        $this->entityManagerMock->expects('commit')->never();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not link user to board: DB Error');

        $this->service->linkBoardToUser($board, $user);
    }
}
