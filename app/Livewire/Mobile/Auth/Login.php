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
            return redirect()->route('mobile.dashboard');
        }
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            request()->session()->regenerate();
            return redirect()->intended(route('mobile.dashboard'));
        }

        $this->addError('email', 'Email atau kata sandi yang Anda masukkan salah. Silakan periksa kembali.');
    }

    public function render()
    {
        return view('livewire.mobile.auth.login');
    }
}
