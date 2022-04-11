<?php

namespace App\Http\Middleware;

use App\Enums\UserRoles;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()) {
            switch (Auth::user()->role) {
                case UserRoles::SUPER_ADMIN:
                {
                    return $next($request);
                }
                case UserRoles::CLIENT:
                {
                    if ($client = Auth::user()->client) {
                        $request->merge(['model_type' => new Client()]);
                        $request->merge(['model_id' => $client->id]);

                        return $next($request);
                    }
                }
            }
        }

        return response()->json('User does not have the right permissions', 403);
    }
}
