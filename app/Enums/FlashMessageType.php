<?php declare(strict_types=1);

namespace App\Enums;

enum FlashMessageType: string
{
    case INFO = 'info';
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
}
