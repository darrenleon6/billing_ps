<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;

class EnsureShiftIsOpen
{
    public function handle(Request $request, Closure $next)
    {
        $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();

        // Pass variabel $activeShift ke view
        view()->share('activeShift', $activeShift);

        return $next($request);
    }
}