<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;

class HorizonDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = '시스템';
    protected static ?string $navigationLabel = 'Horizon 대시보드';
    protected static ?int $navigationSort = 5;
    
    protected static string $view = 'filament.pages.system.horizon-dashboard';
    
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']) ?? false;
    }
    
    public function getHeading(): string
    {
        return 'Laravel Horizon 대시보드';
    }
    
    public function getSubheading(): ?string
    {
        return '백그라운드 작업 큐 모니터링';
    }
} 