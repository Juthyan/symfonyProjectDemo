<?php

declare(strict_types=1);

namespace App\Tests\Formatter;

use App\Entity\Board;
use App\Entity\Role;
use App\Entity\UserRole;
use App\Formatter\BoardFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BoardFormatterTest extends MockeryTestCase
{
    public function testFormatBoard(): void
    {
        // Mock Role
        $roleMock = \Mockery::mock(Role::class);
        $roleMock->shouldReceive('getName')->andReturn('admin');

        // Mock UserRole
        $userRoleMock = \Mockery::mock(UserRole::class);
        $userRoleMock->shouldReceive('getRole')->andReturn($roleMock);

        // Mock Board
        $boardMock = \Mockery::mock(Board::class);
        $boardMock->expects('getId')->andReturn(123);
        $boardMock->expects('getName')->andReturn('Test Board');
        $boardMock->expects('getUserRoles')->andReturn(new ArrayCollection([$userRoleMock]));

        $formatter = new BoardFormatter();
        $result = $formatter->formatBoard($boardMock);

        $expected = [
            'id' => 123,
            'name' => 'Test Board',
            'role' => [
                ['role' => 'admin'],
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
