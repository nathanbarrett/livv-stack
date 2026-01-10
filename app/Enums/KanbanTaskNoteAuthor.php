<?php

declare(strict_types=1);

namespace App\Enums;

enum KanbanTaskNoteAuthor: string
{
    case User = 'user';
    case Ai = 'ai';
}
