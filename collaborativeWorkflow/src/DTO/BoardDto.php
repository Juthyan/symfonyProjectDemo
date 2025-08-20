<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class BoardDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    /**
     * @var int[]
     */
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    public array $userRoleIds = [];

    public function __construct(string $name, array $userRoleIds = [])
    {
        $this->name = $name;
        $this->userRoleIds = $userRoleIds;
    }
}
