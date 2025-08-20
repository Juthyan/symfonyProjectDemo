<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Formatter\UserFormatter;
use App\Services\UserService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UserControllerTest extends MockeryTestCase
{
    public function testFetchAllUsers(): void
    {
        $userMock = \Mockery::mock(User::class);
        $formattedUser = ['id' => 1, 'username' => 'johndoe', 'role' => []];

        $userServiceMock = \Mockery::mock(UserService::class);
        $userServiceMock->expects('fetchUsers')
            ->once()
            ->andReturn([$userMock]);

        $userFormatterMock = \Mockery::mock(UserFormatter::class);
        $userFormatterMock->expects('formatUser')
            ->once()
            ->with($userMock)
            ->andReturn($formattedUser);

        $controller = new UserController($userServiceMock, $userFormatterMock);

        $response = $controller->fetchAllUsers();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode([$formattedUser]), $response->getContent());
    }

    public function testFetchUser(): void
    {
        $userMock = \Mockery::mock(User::class);
        $formattedUser = ['id' => 1, 'username' => 'johndoe', 'role' => []];

        $userServiceMock = \Mockery::mock(UserService::class);
        $userServiceMock->expects('fetchUserByUsername')
            ->once()
            ->with('johndoe')
            ->andReturn($userMock);

        $userFormatterMock = \Mockery::mock(UserFormatter::class);
        $userFormatterMock->expects('formatUser')
            ->once()
            ->with($userMock)
            ->andReturn($formattedUser);

        $controller = new UserController($userServiceMock, $userFormatterMock);

        $response = $controller->fetchUser('johndoe');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode($formattedUser), $response->getContent());
    }
}
