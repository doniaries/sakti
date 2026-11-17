<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\PengaturanAplikasi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (PengaturanAplikasi::isModuleEnabled($module)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini sedang dinonaktifkan oleh Administrator.',
            ], 403);
        }

        if ($request->is('mobile/*')) {
            return redirect()
                ->route('mobile.dashboard')
                ->with('notify', [
                    'type' => 'warning',
                    'title' => 'Fitur Dinonaktifkan',
                    'message' => 'Fitur ini sedang dinonaktifkan sementara oleh Administrator.',
                ]);
        }

        abort(403, 'Fitur ini sedang dinonaktifkan oleh Administrator.');
    }
}
