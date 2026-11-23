<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_id'   => 'nullable|string|max:128',
            'device_name' => 'nullable|string|max:150',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $matchedUser = \App\Models\User::findByLoginEmail($email);

        if (! Auth::attempt(['email' => $matchedUser?->email ?? $email, 'password' => $request->input('password')])) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak ditemukan di database.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Hubungi Admin Kepegawaian.',
            ], 403);
        }

        $deviceId = trim((string) ($request->input('device_id') ?? $request->header('X-Device-Id', '')));
        $deviceName = trim((string) ($request->input('device_name') ?? $request->header('X-Device-Name', '')));

        if (Cache::get('sakti_device_binding_enabled', true) && $deviceId !== '') {
            $deviceCheck = $user->bindOrVerifyDevice($deviceId, $deviceName !== '' ? $deviceName : null);
            if (! $deviceCheck['ok']) {
                return response()->json([
                    'success' => false,
                    'code'    => 'DEVICE_MISMATCH',
                    'message' => $deviceCheck['message'],
                ], 403);
            }
        }

        $isSuperAdminBypass = strtolower((string) $user->email) === 'superadmin@gmail.com'
            || $user->hasRole('super_admin');
        if (! $isSuperAdminBypass) {
            $user->tokens()->where('name', 'mobile-app')->delete();
        }
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
                    'avatar_url'   => $user->avatar_image_url,
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
                'avatar_url'   => $user->avatar_image_url,
                'roles'        => $user->getRoleNames(),
            ],
        ]);
    }

    public function meBiodata(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing([
            'jabatan',
            'bidang',
            'atasan',
            'jadwalKerja',
            'riwayatGolongan',
            'riwayatJabatan',
            'riwayatPendidikan',
        ]);

        $latestGol = $user->riwayatGolongan->firstWhere('is_golongan_terakhir', true)
            ?? $user->riwayatGolongan->first();
        $latestPend = $user->riwayatPendidikan->firstWhere('is_pendidikan_terakhir', true)
            ?? $user->riwayatPendidikan->last();

        return response()->json([
            'success' => true,
            'data' => [
                'utama' => [
                    'NIP' => $user->nip ?: '-',
                    'Jenis Kelamin' => (string) ($user->jenis_kelamin?->value ?? ($user->jenis_kelamin ?? '-')),
                    'Tempat, Tgl Lahir' => trim(($user->tempat_lahir ?: '-') . ', ' . ($user->tanggal_lahir?->format('d/m/Y') ?? '-')),
                    'Agama' => $user->agama ?: '-',
                    'No. HP / WhatsApp' => $user->no_hp ?: '-',
                    'Email Login' => $user->email ?: '-',
                    'Email Resmi' => $user->email_resmi ?: '-',
                    'Alamat' => $user->alamat ?: '-',
                ],
                'golongan' => [
                    'Golongan / Ruang' => (string) ($latestGol?->golongan?->value ?? ($latestGol?->golongan ?? ($user->golongan ?: '-'))),
                    'Nama Pangkat' => $latestGol?->nama_pangkat ?: '-',
                    'TMT Golongan' => $latestGol?->tmt_golongan?->format('d/m/Y') ?? '-',
                    'Nomor SK' => $latestGol?->nomor_sk ?: '-',
                ],
                'jabatan' => [
                    'Jabatan Saat Ini' => $user->jabatan?->nama ?? '-',
                    'Bidang / Unit' => $user->bidang?->nama ?? '-',
                    'Atasan Langsung' => $user->atasan?->name ?? '-',
                    'Status Kerja' => strtoupper((string) ($user->status_kerja ?? 'WFO')),
                    'Jadwal Kerja' => $user->jadwalKerja?->nama ?? 'Jadwal Reguler',
                ],
                'pendidikan' => [
                    'Jenjang Terakhir' => (string) ($latestPend?->jenjang ?? ($user->jenjang_pendidikan ?: '-')),
                    'Jurusan / Prodi' => $latestPend?->jurusan ?: ($user->pendidikan ?: '-'),
                    'Institusi / Sekolah' => $latestPend?->nama_sekolah ?: '-',
                    'Tahun Lulus' => (string) ($latestPend?->tahun_lulus ?? '-'),
                ],
                'keluarga' => [
                    'Status Pernikahan' => $user->status_pernikahan ?: '-',
                    'Nama Pasangan' => $user->nama_pasangan ?: '-',
                    'Jumlah Anak' => (string) ($user->jumlah_anak ?? '-'),
                    'Alamat Keluarga' => $user->alamat_keluarga ?: '-',
                ],
            ],
        ]);
    }

    public function updateBiodata(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $section = (string) $request->input('section', 'utama');
        $fields = $request->input('fields', []);

        if (! is_array($fields)) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak valid.',
            ], 422);
        }

        $clean = fn (string $key): ?string => isset($fields[$key]) && trim((string) $fields[$key]) !== '' && trim((string) $fields[$key]) !== '-'
            ? trim((string) $fields[$key])
            : null;

        if ($section === 'utama') {
            $user->update(array_filter([
                'no_hp' => $clean('No. HP / WhatsApp'),
                'agama' => $clean('Agama'),
                'alamat' => $clean('Alamat'),
                'email_resmi' => $clean('Email Resmi'),
            ], fn ($v) => $v !== null));
        } elseif ($section === 'golongan') {
            $user->update(array_filter([
                'golongan' => $clean('Golongan / Ruang'),
            ], fn ($v) => $v !== null));
        } elseif ($section === 'pendidikan') {
            $user->update(array_filter([
                'jenjang_pendidikan' => $clean('Jenjang Terakhir'),
                'pendidikan' => $clean('Jurusan / Prodi'),
            ], fn ($v) => $v !== null));
        } elseif ($section === 'keluarga') {
            $user->update([
                'status_pernikahan' => $clean('Status Pernikahan'),
                'nama_pasangan' => $clean('Nama Pasangan'),
                'jumlah_anak' => is_numeric($fields['Jumlah Anak'] ?? null)
                    ? (int) $fields['Jumlah Anak']
                    : $user->jumlah_anak,
                'alamat_keluarga' => $clean('Alamat Keluarga'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kepegawaian berhasil diperbarui dan tersinkron ke sistem.',
        ]);
    }

    public function meBiodata(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing([
            'jabatan',
            'bidang',
            'atasan',
            'jadwalKerja',
            'riwayatGolongan',
            'riwayatJabatan',
            'riwayatPendidikan',
        ]);

        $latestGol = $user->riwayatGolongan->firstWhere('is_golongan_terakhir', true)
            ?? $user->riwayatGolongan->first();
        $latestPend = $user->riwayatPendidikan->firstWhere('is_pendidikan_terakhir', true)
            ?? $user->riwayatPendidikan->last();

        return response()->json([
            'success' => true,
            'data' => [
                'utama' => [
                    'NIP' => $user->nip ?: '-',
                    'Jenis Kelamin' => (string) ($user->jenis_kelamin?->value ?? ($user->jenis_kelamin ?? '-')),
                    'Tempat, Tgl Lahir' => trim(($user->tempat_lahir ?: '-') . ', ' . ($user->tanggal_lahir?->format('d/m/Y') ?? '-')),
                    'Agama' => $user->agama ?: '-',
                    'No. HP / WhatsApp' => $user->no_hp ?: '-',
                    'Email Login' => $user->email ?: '-',
                    'Email Resmi' => $user->email_resmi ?: '-',
                    'Alamat' => $user->alamat ?: '-',
                ],
                'golongan' => [
                    'Golongan / Ruang' => (string) ($latestGol?->golongan?->value ?? ($latestGol?->golongan ?? ($user->golongan ?: '-'))),
                    'Nama Pangkat' => $latestGol?->nama_pangkat ?: '-',
                    'TMT Golongan' => $latestGol?->tmt_golongan?->format('d/m/Y') ?? '-',
                    'Nomor SK' => $latestGol?->nomor_sk ?: '-',
                ],
                'jabatan' => [
                    'Jabatan Saat Ini' => $user->jabatan?->nama ?? '-',
                    'Bidang / Unit' => $user->bidang?->nama ?? '-',
                    'Atasan Langsung' => $user->atasan?->name ?? '-',
                    'Status Kerja' => strtoupper((string) ($user->status_kerja ?? 'WFO')),
                    'Jadwal Kerja' => $user->jadwalKerja?->nama ?? 'Jadwal Reguler',
                ],
                'pendidikan' => [
                    'Jenjang Terakhir' => (string) ($latestPend?->jenjang ?? ($user->jenjang_pendidikan ?: '-')),
                    'Jurusan / Prodi' => $latestPend?->jurusan ?: ($user->pendidikan ?: '-'),
                    'Institusi / Sekolah' => $latestPend?->nama_sekolah ?: '-',
                    'Tahun Lulus' => (string) ($latestPend?->tahun_lulus ?? '-'),
                ],
                'keluarga' => [
                    'Status Pernikahan' => $user->status_pernikahan ?: '-',
                    'Nama Pasangan' => $user->nama_pasangan ?: '-',
                    'Jumlah Anak' => (string) ($user->jumlah_anak ?? '-'),
                    'Alamat Keluarga' => $user->alamat_keluarga ?: '-',
                ],
            ],
        ]);
    }

    public function updateBiodata(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $section = (string) $request->input('section', 'utama');
        $fields = $request->input('fields', []);

        if (! is_array($fields)) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak valid.',
            ], 422);
        }

        $clean = fn (string $key): ?string => isset($fields[$key]) && trim((string) $fields[$key]) !== '' && trim((string) $fields[$key]) !== '-'
            ? trim((string) $fields[$key])
            : null;

        if ($section === 'utama') {
            $user->update(array_filter([
                'no_hp' => $clean('No. HP / WhatsApp'),
                'agama' => $clean('Agama'),
                'alamat' => $clean('Alamat'),
                'email_resmi' => $clean('Email Resmi'),
            ], fn ($v) => $v !== null));
        } elseif ($section === 'golongan') {
            $user->update(array_filter([
                'golongan' => $clean('Golongan / Ruang'),
            ], fn ($v) => $v !== null));
        } elseif ($section === 'pendidikan') {
            $user->update(array_filter([
                'jenjang_pendidikan' => $clean('Jenjang Terakhir'),
                'pendidikan' => $clean('Jurusan / Prodi'),
            ], fn ($v) => $v !== null));
        } elseif ($section === 'keluarga') {
            $user->update([
                'status_pernikahan' => $clean('Status Pernikahan'),
                'nama_pasangan' => $clean('Nama Pasangan'),
                'jumlah_anak' => is_numeric($fields['Jumlah Anak'] ?? null)
                    ? (int) $fields['Jumlah Anak']
                    : $user->jumlah_anak,
                'alamat_keluarga' => $clean('Alamat Keluarga'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kepegawaian berhasil diperbarui dan tersinkron ke sistem.',
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $path = $request->file('avatar')->store("dokumen_pegawai/{$user->id}/avatars", 'public');
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->update(['avatar_url' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data' => [
                'avatar_url' => $user->fresh()->avatar_image_url,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    public function getDeviceBinding(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => (bool) Cache::get('sakti_device_binding_enabled', true),
            ],
        ]);
    }

    public function setDeviceBinding(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $enabled = $request->boolean('enabled', true);
        Cache::forever('sakti_device_binding_enabled', $enabled);

        return response()->json([
            'success' => true,
            'message' => $enabled
                ? 'Pembatasan 1 akun 1 perangkat diaktifkan.'
                : 'Pembatasan 1 akun 1 perangkat dinonaktifkan.',
            'data' => [
                'enabled' => $enabled,
            ],
        ]);
    }
}
