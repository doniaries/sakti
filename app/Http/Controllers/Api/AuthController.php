<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $identity = trim((string) $request->input('email'));
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        if (! Auth::attempt([$field => $identity, 'password' => $request->input('password')])) {
            return response()->json([
                'success' => false,
                'message' => 'NIP/Email atau kata sandi tidak ditemukan di database.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
            ],
        ]);
    }

    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }
}
