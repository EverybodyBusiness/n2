<?php

namespace App\Filament\Resources\System\ScheduledTaskResource\Pages;

use App\Filament\Resources\System\ScheduledTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateScheduledTask extends CreateRecord
{
    protected static string $resource = ScheduledTaskResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 크론 표현식 유효성 추가 검증
        if (!$this->getModel()::isValidExpression($data['expression'])) {
            throw new \Exception('유효하지 않은 크론 표현식입니다.');
        }

        return $data;
    }
} 