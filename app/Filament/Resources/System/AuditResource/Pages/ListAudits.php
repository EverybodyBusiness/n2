<?php

namespace App\Filament\Resources\System\AuditResource\Pages;

use App\Filament\Resources\System\AuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 감사 로그는 수동으로 생성할 수 없음
        ];
    }

    public function getTitle(): string
    {
        return '감사 로그';
    }

    public function getSubheading(): ?string
    {
        return '시스템의 모든 변경 사항과 사용자 활동을 추적합니다.';
    }
} 