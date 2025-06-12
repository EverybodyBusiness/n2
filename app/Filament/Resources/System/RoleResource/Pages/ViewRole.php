<?php

namespace App\Filament\Resources\System\RoleResource\Pages;

use App\Filament\Resources\System\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function getSubheading(): ?string
    {
        return '역할의 상세 정보와 할당된 권한을 확인합니다.';
    }
}
