<?php

declare(strict_types=1);

namespace App\DTO;

use App\Utils\Sanitizer;
use Symfony\Component\Validator\Constraints as Assert;

class TaskDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[Assert\Length(
        max: 255,
        maxMessage: 'Description cannot be longer than {{ limit }} characters.'
    )]    
    private ?string $description;

    #[Assert\Type('integer')]
    private int $boardId;

    #[Assert\Type('integer')]
    private ?int $stateId;

    public function __construct(string $name, ?string $description, int $boardId, ?int $stateId)
    {
        $this->name = $name;
        $this->description = Sanitizer::sanitizeText($description);
        $this->boardId = $boardId;
        $this->stateId = $stateId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getBoardId()
    {
        return $this->boardId;
    }

    public function getStateId()
    {
        return $this->stateId;
    }
}
