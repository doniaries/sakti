<?php

namespace App\Filament\Resources\Bidangs\RelationManagers;

use App\Models\Bidang;
use App\Models\MasterKegiatan;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubBidangsRelationManager extends RelationManager
{
    protected static string $relationship = 'subBidangs';

    protected static ?string $title = 'Sub Bidang & Kegiatan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('nama')
                    ->label('Nama Sub Bidang')
                    ->required()
                    ->maxLength(255),

                Select::make('kepala_id')
                    ->label('Kepala Sub Bidang (Opsional)')
                    ->options(function (RelationManager $livewire): array {
                        /** @var Bidang $bidang */
                        $bidang = $livewire->getOwnerRecord();

                        return User::query()->where('bidang_id', $bidang->getKey())->orderBy('name')->pluck('name', 'id')->all();
                    })
                    ->searchable(),

                Select::make('kegiatans')
                    ->label('Kegiatan yang Dikelola')
                    ->relationship('kegiatans', 'nama_kegiatan', function (Builder $query, RelationManager $livewire): Builder {
                        /** @var Bidang $bidang */
                        $bidang = $livewire->getOwnerRecord();

                        return $query->where('bidang_id', $bidang->getKey());
                    })
                    ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record): string => $record->label)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Satu kegiatan boleh dikelola oleh lebih dari satu sub bidang.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('kepala')->withCount(['users', 'kegiatans']))
            ->columns([
                TextColumn::make('nama')
                    ->label('Sub Bidang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('kepala.name')
                    ->label('Kepala Sub Bidang')
                    ->placeholder('-'),
                TextColumn::make('users_count')
                    ->label('Jumlah Staf')
                    ->badge()
                    ->sortable(),
                TextColumn::make('kegiatans_count')
                    ->label('Jumlah Kegiatan')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Sub Bidang'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

