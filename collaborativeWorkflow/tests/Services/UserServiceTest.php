<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\UserService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UserServiceTest extends MockeryTestCase
{
    private $userRepositoryMock;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->userService = new UserService($this->userRepositoryMock);
    }

    public function testFetchUsersReturnsArray()
    {
        $users = [
            Mockery::mock(User::class),
            Mockery::mock(User::class),
        ];

        $this->userRepositoryMock
            ->expects('findAll')
            ->once()
            ->andReturn($users);

        $result = $this->userService->fetchUsers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testFetchUserByUsernameReturnsUser()
    {
        $userMock = Mockery::mock(User::class);

        $this->userRepositoryMock
            ->expects('findOneBy')
            ->with(['userName' => 'johndoe'])
            ->once()
            ->andReturn($userMock);

        $result = $this->userService->fetchUserByUsername('johndoe');

        $this->assertSame($userMock, $result);
    }
}
