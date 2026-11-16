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
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim((string) $request->input('email')));

        if (! Auth::attempt(['email' => $email, 'password' => $request->input('password')])) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak ditemukan di database.',
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
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'nip'          => $user->nip,
                    'jabatan'      => $user->jabatan?->nama ?? 'Pegawai',
                    'status_kerja' => $user->status_kerja ?? 'wfo',
                    'roles'        => $user->getRoleNames(),
                ],
            ],
        ]);
    }

    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing('jabatan');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'nip'          => $user->nip,
                'jabatan'      => $user->jabatan?->nama ?? 'Pegawai',
                'status_kerja' => $user->status_kerja ?? 'wfo',
                'roles'        => $user->getRoleNames(),
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
