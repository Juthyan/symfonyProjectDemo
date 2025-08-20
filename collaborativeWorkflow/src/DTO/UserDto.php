<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Username must contain only letters, numbers and underscores.')]
    private string $userName;

    #[Assert\NotBlank]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    private string $mail;

    public function __construct(string $userName, string $mail)
    {
        $this->userName = $userName;
        $this->mail = $mail;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }
}
