<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FlashLocation;
use App\Enums\FlashMessageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function redirectWithFlashInfo(Request $request): RedirectResponse
    {
        $type = FlashMessageType::tryFrom($request->get('type', 'info'));

        if (! $type) {
            return redirect()->route('home')->with('error', 'Invalid flash message type');
        }

        return redirect()->route('home')
            ->with($type->value, 'This is a '.$type->value.' message from the server!')
            ->with(FlashLocation::sessionKey(), FlashLocation::TOP_LEFT->value);
    }
}
