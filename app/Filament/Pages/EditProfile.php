<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Avatar')
                    ->avatar()
                    ->image()
                    ->disk('public')
                    ->directory('avatars')
                    ->maxSize(2048)
                    ->columnSpanFull(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getRedirectUrl(): ?string
    {
        return filament()->getUrl();
    }
}
