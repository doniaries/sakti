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
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
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

        $this->addError('email', 'Akun tidak cocok dengan data kami.');
    }

    public function render()
    {
        return view('livewire.mobile.auth.login');
    }
}
