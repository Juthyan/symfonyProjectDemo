<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\DTO\UserDto;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $userName = 'valid_user123';
        $email = 'valid@example.com';

        $userDto = new UserDto($userName, $email);

        $this->assertSame($userName, $userDto->getUserName());
        $this->assertSame($email, $userDto->getMail());
    }
}
