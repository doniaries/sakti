<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectNonAdminToMobile
{
    /**
     * Role yang diizinkan mengakses panel admin Filament (/admin).
     *
     * @var array<int, string>
     */
    private const ALLOWED_ADMIN_ROLES = [
        'super_admin',
        'admin',
        'admin_kepegawaian',
        'admin_keuangan',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->hasAnyRole(self::ALLOWED_ADMIN_ROLES)) {
            return $next($request);
        }

        return redirect()->route('mobile.dashboard');
    }
}
