<?php

namespace App\Filament\Resources\System\MediaResource\Pages;

use App\Filament\Resources\System\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getSubheading(): ?string
    {
        return '시스템에 업로드된 모든 미디어 파일을 관리합니다.';
    }
} 