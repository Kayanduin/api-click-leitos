<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidateUserUnit
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
        $userUnit = $user->userUnitObject();
        if (is_null($userUnit)) {
            $errorMessage = 'This user does not have a Samu Unit or ' .
                'Health Unit attached to him, please create a Samu unit or Health unit first.';
            return new Response(
                ['message' => $errorMessage],
                403
            );
        }
        return $next($request);
    }
}
