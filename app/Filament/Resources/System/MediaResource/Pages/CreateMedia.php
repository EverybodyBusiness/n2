<?php

namespace App\Filament\Resources\System\MediaResource\Pages;

use App\Filament\Resources\System\MediaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;
    
    public function getSubheading(): ?string
    {
        return '새로운 미디어 파일을 업로드합니다.';
    }
} 