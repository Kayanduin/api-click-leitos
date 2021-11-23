<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FirstUser
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
        $userService = new UserService();
        if (!empty($userService->getAllUsers())) {
            return new Response(['message' => 'Access denied.'], 403);
        }
        return $next($request);
    }
}
