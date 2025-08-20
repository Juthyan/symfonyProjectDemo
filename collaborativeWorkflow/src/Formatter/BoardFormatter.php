<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Entity\Board;

class BoardFormatter
{
    public function formatBoard(Board $board): array
    {
        $userRoles = [];

        foreach ($board->getUserRoles() as $userRole) {
            $userRoles[] = [
                'role' => $userRole->getRole()->getName(), // Assumes Role has getName()
            ];
        }

        return [
            'id' => $board->getId(),
            'name' => $board->getName(),
            'role' => $userRoles,
        ];
    }
}
