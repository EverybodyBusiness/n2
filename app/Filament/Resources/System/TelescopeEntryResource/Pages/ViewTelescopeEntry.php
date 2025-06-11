<?php

namespace App\Filament\Resources\System\TelescopeEntryResource\Pages;

use App\Filament\Resources\System\TelescopeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTelescopeEntry extends ViewRecord
{
    protected static string $resource = TelescopeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit action for Telescope entries
        ];
    }
}
