<?php

namespace App\Filament\Resources\System\MediaResource\Pages;

use App\Filament\Resources\System\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMedia extends ViewRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
} 