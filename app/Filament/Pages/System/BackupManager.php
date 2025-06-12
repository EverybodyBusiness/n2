<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Jobs\System\BackupJob;
use Carbon\Carbon;

class BackupManager extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'system/backup';
    
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = '시스템';
    protected static ?string $navigationLabel = '백업';
    protected static ?int $navigationSort = 60;
    
    protected static string $view = 'filament.pages.system.backup-manager';
    
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
    
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                // 백업 파일 목록 가져오기
                $backups = collect();
                $disk = Storage::disk('local');
                $files = $disk->files(config('backup.backup.name'));
                
                foreach ($files as $file) {
                    if (str_ends_with($file, '.zip')) {
                        $backups->push([
                            'name' => basename($file),
                            'path' => $file,
                            'size' => $disk->size($file),
                            'created_at' => Carbon::createFromTimestamp($disk->lastModified($file)),
                        ]);
                    }
                }
                
                return $backups->sortByDesc('created_at');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('백업 파일명')
                    ->searchable(),
                    
                TextColumn::make('size')
                    ->label('크기')
                    ->formatStateUsing(fn ($state) => $this->formatBytes($state)),
                    
                TextColumn::make('created_at')
                    ->label('생성일시')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Action::make('download')
                    ->label('다운로드')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        return Storage::disk('local')->download($record['path']);
                    }),
                    
                Action::make('delete')
                    ->label('삭제')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        Storage::disk('local')->delete($record['path']);
                        Notification::make()
                            ->title('백업 삭제됨')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
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
            ]);
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