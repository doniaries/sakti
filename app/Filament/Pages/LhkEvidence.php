<?php

namespace App\Filament\Pages;

use BackedEnum;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class LhkEvidence extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'LHK Evidence (Absensi)';
    protected static ?string $title = 'Absensi Bulanan (Evidence)';
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.lhk-evidence';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pilih Bulan Pembuatan LHK (EVIDENCE)')
                    ->schema([
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options([
                                '2026' => '2026',
                                '2025' => '2025',
                            ])
                            ->default(now()->year)
                            ->required(),
                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->default(now()->format('m'))
                            ->required(),
                    ])
            ])
            ->statePath('data');
    }

    public function lihatLhk()
    {
        $data = $this->form->getState();
        // Nanti kita tambahkan logika untuk memuat data tabel berdasarkan $data['tahun'] dan $data['bulan']
    }
}
