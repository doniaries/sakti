<?php

namespace App\Livewire\Mobile\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Login - Absensi LHK Mobile')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    protected array $messages = [
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format alamat email tidak valid.',
        'password.required' => 'Kata sandi wajib diisi.',
    ];

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->route('mobile.absensi');
        }
    }

    public function login()
    {
        $this->validate();

        $email = strtolower(trim($this->email));
        $user = \App\Models\User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            $this->addError('email', "Email ({$email}) tidak terdaftar atau berbeda dengan email akun Anda. Silakan periksa kembali atau hubungi Admin Kepegawaian.");
            return;
        }

        if (! $user->aktif) {
            $this->addError('email', 'Akun pegawai Anda sedang dinonaktifkan. Silakan hubungi Administrator Kepegawaian.');
            return;
        }

        if (Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
            request()->session()->regenerate();
            return redirect()->intended(route('mobile.absensi'));
        }

        $this->addError('password', 'Kata sandi yang Anda masukkan salah. Silakan periksa kembali.');
    }

    public function render()
    {
        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
        $tema = request()->query('tema', $pengaturan->portal_theme ?? 'default');

        return view($tema === 'rhoe' ? 'livewire.mobile.auth.login-rhoe' : 'livewire.mobile.auth.login');
    }
}
