<?php

declare(strict_types=1);

namespace App\Tests\Formatter;

use App\Entity\User;
use App\Formatter\UserFormatter;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UserFormatterTest extends MockeryTestCase
{
    public function testFormatUser(): void
    {
        // Mock UserRoles collection
        $userRolesMock = \Mockery::mock(Collection::class);
        $userRolesMock->shouldReceive('toArray')
            ->once()
            ->andReturn([
                ['role' => 'admin', 'board' => ['id' => 1, 'name' => 'Board 1']],
                ['role' => 'viewer', 'board' => ['id' => 2, 'name' => 'Board 2']],
            ]);

        // Mock User entity
        $userMock = \Mockery::mock(User::class);
        $userMock->expects('getId')->once()->andReturn(123);
        $userMock->expects('getUserName')->once()->andReturn('johndoe');
        $userMock->expects('getUserRoles')->once()->andReturn($userRolesMock);

        $formatter = new UserFormatter();

        $expected = [
            'id' => 123,
            'username' => 'johndoe',
            'role' => [
                ['role' => 'admin', 'board' => ['id' => 1, 'name' => 'Board 1']],
                ['role' => 'viewer', 'board' => ['id' => 2, 'name' => 'Board 2']],
            ],
        ];

        $this->assertEquals($expected, $formatter->formatUser($userMock));
    }
}
