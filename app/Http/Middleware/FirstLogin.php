<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FirstLogin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->first_time_login === 1) {
            return new Response(['message' => 'First login, please reset your password first.'], 403);
        }
        return $next($request);
    }
}
