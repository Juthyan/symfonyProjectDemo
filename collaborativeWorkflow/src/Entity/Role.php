<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 50, unique: true)]
    private string $name;

    #[ORM\Column(type: 'boolean')]
    private bool $canEdit = false;

    #[ORM\Column(type: 'boolean')]
    private bool $canDelete = false;

    #[ORM\Column(type: 'boolean')]
    private bool $canInvite = false;

    /**
     * Get the value of id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name.
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of canEdit.
     */
    public function getCanEdit(): bool
    {
        return $this->canEdit;
    }

    /**
     * Set the value of canEdit.
     *
     * @return self
     */
    public function setCanEdit(bool $canEdit)
    {
        $this->canEdit = $canEdit;

        return $this;
    }

    /**
     * Get the value of canDelete.
     */
    public function getCanDelete()
    {
        return $this->canDelete;
    }

    /**
     * Set the value of canDelete.
     *
     * @return self
     */
    public function setCanDelete(bool $canDelete)
    {
        $this->canDelete = $canDelete;

        return $this;
    }

    /**
     * Get the value of canInvite.
     */
    public function getCanInvite(): bool
    {
        return $this->canInvite;
    }

    /**
     * Set the value of canInvite.
     *
     * @return self
     */
    public function setCanInvite(bool $canInvite)
    {
        $this->canInvite = $canInvite;

        return $this;
    }
}
