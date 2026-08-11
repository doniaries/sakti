<?php

$userFormPath = 'c:/laragon/www/absensilhk/app/Filament/Resources/Users/Schemas/UserForm.php';
$userFormContent = file_get_contents($userFormPath);
$userFormContent = str_replace(
    "\Filament\Schemas\Components\Toggle::make('aktif')",
    "\Filament\Schemas\Components\Select::make('roles')
                    ->relationship('roles', 'name', fn (\$query) => auth()->user()->hasRole('super_admin') ? \$query : \$query->where('name', '!=', 'super_admin'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                \Filament\Schemas\Components\Toggle::make('aktif')",
    $userFormContent
);

$userFormContent = str_replace(
    "->required(fn (\$operation) => \$operation === 'create')
                    ->maxLength(255),",
    "->required(fn (\$operation) => \$operation === 'create')
                    ->maxLength(255)
                    ->dehydrated(fn (\$state) => filled(\$state)),",
    $userFormContent
);
file_put_contents($userFormPath, $userFormContent);

$userResourcePath = 'c:/laragon/www/absensilhk/app/Filament/Resources/Users/UserResource.php';
$userResourceContent = file_get_contents($userResourcePath);
$eloquentQuery = "
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        \$query = parent::getEloquentQuery();
        
        /** @var \App\Models\User|null \$user */
        \$user = auth()->user();
        if (\$user && !\$user->hasRole('super_admin')) {
            \$query->whereDoesntHave('roles', function (\$q) {
                \$q->where('name', 'super_admin');
            });
        }
        
        return \$query;
    }
";
$userResourceContent = str_replace(
    "public static function getRelations(): array",
    $eloquentQuery . "\n    public static function getRelations(): array",
    $userResourceContent
);
file_put_contents($userResourcePath, $userResourceContent);


$usersTablePath = 'c:/laragon/www/absensilhk/app/Filament/Resources/Users/Tables/UsersTable.php';
$usersTableContent = file_get_contents($usersTablePath);
$usersTableContent = str_replace(
    "\Filament\Tables\Columns\ToggleColumn::make('aktif'),",
    "\Filament\Tables\Columns\TextColumn::make('roles.name')->badge(),
                \Filament\Tables\Columns\ToggleColumn::make('aktif'),",
    $usersTableContent
);
file_put_contents($usersTablePath, $usersTableContent);

echo "Modifications applied successfully.\n";
