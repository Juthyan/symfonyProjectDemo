<?php

declare(strict_types=1);

namespace App\Enum;

enum BoardRole: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
}
