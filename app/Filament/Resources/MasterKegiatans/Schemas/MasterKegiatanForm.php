<?php

namespace App\Filament\Resources\MasterKegiatans\Schemas;

use App\Models\Bidang;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MasterKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bidang_id')
                    ->label('Bidang Pengampu Kegiatan')
                    ->default(function (): ?int {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user) {
                            return null;
                        }
                        if (filled($user->bidang_id)) {
                            return (int) $user->bidang_id;
                        }
                        $kepalaBidangId = Bidang::where('kepala_id', $user->id)->value('id');

                        return $kepalaBidangId ? (int) $kepalaBidangId : null;
                    })
                    ->options(fn () => Bidang::pluck('nama', 'id')->all())
                    ->searchable()
                    ->required(),

                TextInput::make('kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->placeholder('Contoh: 1.01.02.1.01')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('nama_kegiatan')
                    ->label('Nomenklatur Kegiatan')
                    ->placeholder('Contoh: Program Pembinaan dan Pengembangan Olahraga')
                    ->required(),

                TextInput::make('pagu_anggaran')
                    ->label('Jumlah Uang / Pagu Kegiatan (Rp)')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp')
                    ->required(),
            ]);
    }
}
