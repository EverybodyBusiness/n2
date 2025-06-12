<?php

namespace App\Filament\Resources\System\ScheduledTaskResource\Pages;

use App\Filament\Resources\System\ScheduledTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledTask extends EditRecord
{
    protected static string $resource = ScheduledTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->is_system) {
                        throw new \Exception('시스템 작업은 삭제할 수 없습니다.');
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 크론 표현식 유효성 추가 검증
        if (!$this->getModel()::isValidExpression($data['expression'])) {
            throw new \Exception('유효하지 않은 크론 표현식입니다.');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
} 