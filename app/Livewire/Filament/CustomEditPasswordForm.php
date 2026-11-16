<?php

namespace App\Livewire\Filament;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasUser;
use Joaopaulolndev\FilamentEditProfile\Livewire\BaseProfileForm;

class CustomEditPasswordForm extends BaseProfileForm
{
    use HasUser;

    protected string $view = 'filament-edit-profile::livewire.edit-password-form';

    public ?array $data = [];

    protected static int $sort = 20;

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Perbarui Kata Sandi')
                    ->aside()
                    ->description('Pastikan akun Anda menggunakan kata sandi yang aman. Anda dapat langsung memasukkan kata sandi baru tanpa perlu memasukkan kata sandi lama.')
                    ->schema([
                        TextInput::make('password')
                            ->label('Kata Sandi Baru')
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->same('passwordConfirmation')
                            ->revealable(),
                        TextInput::make('passwordConfirmation')
                            ->label('Konfirmasi Kata Sandi')
                            ->password()
                            ->required()
                            ->dehydrated(false)
                            ->revealable(),
                    ]),
            ])
            ->model($this->getUser())
            ->statePath('data');
    }

    public function updatePassword(): void
    {
        try {
            $data = $this->form->getState();

            $newData = [
                'password' => $data['password'],
            ];

            $this->user->update($newData);
        } catch (Halt $exception) {
            return;
        }

        if (request()->hasSession() && array_key_exists('password', $data)) {
            $guardName = Filament::getAuthGuard();
            $guard = Filament::auth();
            $freshHash = (string) ($this->user->fresh()?->getAuthPassword() ?? $data['password']);
            $sessionHash = method_exists($guard, 'hashPasswordForCookie')
                ? $guard->hashPasswordForCookie($freshHash)
                : $freshHash;

            request()->session()->put([
                'password_hash_' . $guardName => $sessionHash,
            ]);
        }

        $this->form->fill();

        Notification::make()
            ->success()
            ->title('Kata sandi berhasil diperbarui!')
            ->send();
    }
}

