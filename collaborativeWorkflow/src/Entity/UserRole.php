<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
class UserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Board::class, inversedBy: 'userRoles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Board $board = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userRoles')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Role $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(Board $board): static
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get the value of user.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user.
     *
     * @return self
     */
    public function setUser($user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of role.
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * Set the value of role.
     *
     * @return self
     */
    public function setRole(Role $role): static
    {
        $this->role = $role;

        return $this;
    }
}
