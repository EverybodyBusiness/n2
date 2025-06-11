<?php

namespace App\Filament\Resources\System\TelescopeEntryResource\Pages;

use App\Filament\Resources\System\TelescopeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTelescopeEntries extends ListRecords
{
    protected static string $resource = TelescopeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for Telescope entries
        ];
    }
}
