<?php

namespace App\Entity;

use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoardRepository::class)]
class Board
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'board', targetEntity: UserRole::class, cascade: ['persist', 'remove'])]
    private Collection $userRoles;

    #[ORM\Column(length: 255)]
    private string $name = 'Board';

    #[ORM\OneToMany(mappedBy: 'board', targetEntity: Task::class, cascade: ['persist', 'remove'])]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    /**
     * Set the value of userRoles.
     *
     * @return self
     */
    public function setUserRoles($userRoles)
    {
        $this->userRoles = $userRoles;

        return $this;
    }

    /**
     * Get the value of tasks.
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set the value of tasks.
     *
     * @return self
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }
}
