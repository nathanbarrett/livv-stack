<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function redirectWithFlashInfo(Request $request): RedirectResponse
    {
        $type = $request->get('type', 'info');
        $validator = validator(compact('type'), ['type' => 'in:info,success,error,warning']);
        if ($validator->fails()) {
            return redirect()->route('home')->with('error', 'Invalid flash message type');
        }

        return redirect()->route('home')->with($type, 'This is a '.$type.' message from the server!');
    }
}
