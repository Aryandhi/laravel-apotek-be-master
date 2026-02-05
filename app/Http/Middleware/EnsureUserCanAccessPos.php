<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessPos
{
    /**
     * Roles that can access POS.
     *
     * @var array<UserRole>
     */
    protected array $allowedRoles = [
        UserRole::Owner,
        UserRole::Pharmacist,
        UserRole::Assistant,
        UserRole::Cashier,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('pos.login');
        }

        if (! in_array($user->role, $this->allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki akses ke POS.');
        }

        return $next($request);
    }
}
