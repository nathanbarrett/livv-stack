<?php declare(strict_types=1);

namespace App\Enums;

enum FlashLocation: string
{
    case BOTTOM_CENTER = 'bottom center';
    case BOTTOM_LEFT = 'bottom left';
    case BOTTOM_RIGHT = 'bottom right';
    case TOP_CENTER = 'top center';
    case TOP_LEFT = 'top left';
    case TOP_RIGHT = 'top right';

    public static function sessionKey(): string
    {
        return 'flashMessageLocation';
    }
}
