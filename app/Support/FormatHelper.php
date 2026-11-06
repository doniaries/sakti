<?php

namespace App\Support;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;

class FormatHelper
{
    /**
     * Format angka ke Rupiah standar Indonesia (contoh: Rp 121.212.121).
     */
    public static function rupiah(?float $nominal, bool $withPrefix = true): string
    {
        if ($nominal === null) {
            return '-';
        }

        $formatted = number_format($nominal, 0, ',', '.');

        return $withPrefix ? "Rp {$formatted}" : $formatted;
    }

    /**
     * Konversi string rupiah bertitik ke float murni (contoh: "121.212.121" -> 121212121.0).
     */
    public static function parseRupiah(mixed $value): float
    {
        if (blank($value)) {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $str = str_replace(['Rp', 'rp', 'RP', ' '], '', (string) $value);

        if (str_contains($str, '.') && str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, '.')) {
            $str = str_replace('.', '', $str);
        } elseif (str_contains($str, ',')) {
            $str = str_replace(',', '.', $str);
        }

        return (float) ($str ?: 0);
    }

    /**
     * Komponen TextInput Filament untuk input uang Rupiah dengan masking pemisah ribuan titik.
     */
    public static function rupiahInput(string $name, string $label = 'Nominal'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->prefix('Rp')
            ->placeholder('0')
            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
            ->stripCharacters('.')
            ->formatStateUsing(function ($state) {
                if (blank($state)) {
                    return null;
                }
                return number_format((float) $state, 0, ',', '.');
            })
            ->dehydrateStateUsing(function ($state) {
                return static::parseRupiah($state);
            });
    }

    /**
     * Komponen DatePicker Filament dengan format tanggal Indonesia d/m/Y.
     */
    public static function datePicker(string $name, string $label = 'Tanggal'): DatePicker
    {
        return DatePicker::make($name)
            ->label($label)
            ->displayFormat('d/m/Y')
            ->native(false)
            ->placeholder('DD/MM/YYYY');
    }

    /**
     * Komponen DateTimePicker Filament dengan format tanggal & jam Indonesia d/m/Y H:i.
     */
    public static function dateTimePicker(string $name, string $label = 'Tanggal & Waktu'): DateTimePicker
    {
        return DateTimePicker::make($name)
            ->label($label)
            ->displayFormat('d/m/Y H:i')
            ->native(false)
            ->placeholder('DD/MM/YYYY HH:MM');
    }

    /**
     * Kolom tabel TextColumn untuk uang Rupiah.
     */
    public static function rupiahColumn(string $name, string $label = 'Nominal'): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->formatStateUsing(fn ($state) => filled($state) ? static::rupiah((float) $state) : '-')
            ->sortable();
    }

    /**
     * Kolom tabel TextColumn untuk tanggal Indonesia d/m/Y.
     */
    public static function dateColumn(string $name, string $label = 'Tanggal'): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->date('d/m/Y')
            ->sortable();
    }

    /**
     * Kolom tabel TextColumn untuk tanggal & jam Indonesia d/m/Y H:i.
     */
    public static function dateTimeColumn(string $name, string $label = 'Waktu'): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->dateTime('d/m/Y H:i')
            ->sortable();
    }
}
