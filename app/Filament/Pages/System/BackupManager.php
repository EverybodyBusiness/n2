<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Jobs\System\BackupJob;
use Carbon\Carbon;

class BackupManager extends Page
{
    protected static ?string $slug = 'system/backup';
    
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = '시스템';
    protected static ?string $navigationLabel = '백업';
    protected static ?int $navigationSort = 60;
    
    protected static string $view = 'filament.pages.system.backup-manager';
    
    public $backups = [];
    
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']) ?? false;
    }
    
    public function getHeading(): string
    {
        return '백업 관리';
    }
    
    public function getSubheading(): ?string
    {
        return '시스템 백업을 생성하고 복원 작업을 수행합니다.';
    }
    
    public function mount(): void
    {
        $this->loadBackups();
    }
    
    public function loadBackups(): void
    {
        $this->backups = [];
        $disk = Storage::disk('local');
        
        if (!$disk->exists(config('backup.backup.name'))) {
            return;
        }
        
        $files = $disk->files(config('backup.backup.name'));
        
        foreach ($files as $file) {
            if (str_ends_with($file, '.zip')) {
                $this->backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->formatBytes($disk->size($file)),
                    'created_at' => Carbon::createFromTimestamp($disk->lastModified($file))->format('Y-m-d H:i:s'),
                ];
            }
        }
        
        // 최신 파일이 먼저 오도록 정렬
        $this->backups = collect($this->backups)->sortByDesc('created_at')->values()->toArray();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup_all')
                ->label('전체 백업')
                ->icon('heroicon-o-archive-box')
                ->action(function () {
                    BackupJob::dispatch();
                    Notification::make()
                        ->title('백업 작업이 시작되었습니다')
                        ->body('백업이 완료되면 목록에 표시됩니다.')
                        ->success()
                        ->send();
                }),
                
            Action::make('backup_db')
                ->label('DB만 백업')
                ->icon('heroicon-o-circle-stack')
                ->action(function () {
                    BackupJob::dispatch(['only-db' => true]);
                    Notification::make()
                        ->title('데이터베이스 백업이 시작되었습니다')
                        ->success()
                        ->send();
                }),
                
            Action::make('refresh')
                ->label('새로고침')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->loadBackups()),
        ];
    }
    
    public function downloadBackup(string $path): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk('local')->download($path);
    }
    
    public function deleteBackup(string $path): void
    {
        Storage::disk('local')->delete($path);
        
        Notification::make()
            ->title('백업 삭제됨')
            ->success()
            ->send();
            
        $this->loadBackups();
    }
    
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 