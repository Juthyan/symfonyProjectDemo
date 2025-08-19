<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $userName;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserRole::class, cascade: ['persist', 'remove'])]
    private Collection $userRoles;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private UserSettings $userSetting;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of userName.
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * Set the value of userName.
     *
     * @return self
     */
    public function setUserName(string $userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get the value of userRoles.
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    /**
     * Set the value of userRoles.
     *
     * @return self
     */
    public function setUserRoles(Collection $userRoles)
    {
        $this->userRoles = $userRoles;

        return $this;
    }

    /**
     * Get the value of email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the value of email.
     *
     * @return self
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of userSetting.
     */
    public function getUserSetting(): UserSettings
    {
        return $this->userSetting;
    }

    /**
     * Set the value of userSetting.
     *
     * @return self
     */
    public function setUserSetting(UserSettings $userSetting)
    {
        $this->userSetting = $userSetting;

        return $this;
    }
}
