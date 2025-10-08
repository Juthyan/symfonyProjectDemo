<?php

declare(strict_types=1);

namespace App\Formatter;

class TaskFormatter
{
    public function formatTasks(array $boardTasks): array
    {
        $formattedBardTaks = [];
        foreach ($boardTasks as $boardTask) {
            $formattedBardTaks[] = [
                'name' => $boardTask->getName(),
                'description' => $boardTask->getDescription(),
                'state' => [
                    'id' => $boardTask->getState()->getId(),
                    'name' => $boardTask->getState()->getName(),
                    'order' => $boardTask->getState()->getOrder(),
                ],
            ];
        }

        return $formattedBardTaks;
    }
}
