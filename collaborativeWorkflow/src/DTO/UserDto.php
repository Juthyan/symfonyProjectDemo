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

    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 8, groups: ['create', 'edit'])]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Password must contain at least one uppercase letter, one lowercase letter, and one digit.',
        groups: ['create', 'edit']
    )]
    #[Assert\NotCompromisedPassword(groups: ['create', 'edit'])]
    private ?string $password;

    /**
     * @var int[]
     */
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    public array $userRoleIds = [];

    public function __construct(string $userName, string $mail, string $password, array $userRoleIds = [])
    {
        $this->userName = $userName;
        $this->mail = $mail;
        $this->password = $password;
        $this->userRoleIds = $userRoleIds;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserRoleIds(): array
    {
        return $this->userRoleIds;
    }
}
