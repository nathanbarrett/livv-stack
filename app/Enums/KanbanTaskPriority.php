<?php

declare(strict_types=1);

namespace App\Enums;

enum KanbanTaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
