<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Pages;

use App\Filament\Resources\SuratTugas\SuratTugasResource;
use Filament\Resources\Pages\ListRecords;

class ListSuratTugas extends ListRecords
{
    protected static string $resource = SuratTugasResource::class;
}
