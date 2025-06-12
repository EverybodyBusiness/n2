<?php

namespace App\Filament\Resources\System\AuditResource\Pages;

use App\Filament\Resources\System\AuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAudit extends ViewRecord
{
    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 감사 로그는 수정하거나 삭제할 수 없음
        ];
    }

    public function getTitle(): string
    {
        return '감사 로그 상세';
    }

    public function getSubheading(): ?string
    {
        return '감사 로그의 상세 정보와 변경 내역을 확인합니다.';
    }
} 