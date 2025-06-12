<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;

class HorizonDashboard extends Page
{
    protected static ?string $slug = 'system/background-jobs';
    
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = '시스템';
    protected static ?string $navigationLabel = '백그라운드 작업';
    protected static ?int $navigationSort = 5;
    
    protected static string $view = 'filament.pages.system.horizon-dashboard';
    
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']) ?? false;
    }
    
    public function getHeading(): string
    {
        return '백그라운드 작업 큐 모니터링';
    }
    
    public function getSubheading(): ?string
    {
        return '실행 중인 작업과 큐의 상태를 실시간으로 모니터링합니다.';
    }
} 