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
        $password = 'test';

        $userDto = new UserDto($userName, $email, $password);

        $this->assertSame($userName, $userDto->getUserName());
        $this->assertSame($email, $userDto->getMail());
        $this->assertSame($password, $userDto->getPassword());
    }
}
