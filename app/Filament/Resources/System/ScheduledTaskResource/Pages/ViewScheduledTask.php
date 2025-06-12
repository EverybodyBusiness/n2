<?php

namespace App\Filament\Resources\System\ScheduledTaskResource\Pages;

use App\Filament\Resources\System\ScheduledTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;

class ViewScheduledTask extends ViewRecord
{
    protected static string $resource = ScheduledTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle')
                ->label(fn () => $this->record->is_active ? '비활성화' : '활성화')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn () => $this->record->is_active ? 'warning' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->toggleActive();
                    $this->refreshFormData(['is_active']);
                }),
                
            Actions\Action::make('run')
                ->label('즉시 실행')
                ->icon('heroicon-o-play-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->runNow('manual', auth()->id());
                }),
                
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('기본 정보')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('작업 이름'),
                                    
                                TextEntry::make('type')
                                    ->label('작업 유형')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'command' => 'info',
                                        'job' => 'success',
                                        'closure' => 'warning',
                                    }),
                                    
                                TextEntry::make('category')
                                    ->label('카테고리')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                            
                        TextEntry::make('command')
                            ->label('실행 명령/클래스')
                            ->copyable(),
                            
                        TextEntry::make('description')
                            ->label('설명')
                            ->default('-'),
                    ]),
                    
                Section::make('스케줄 정보')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('expression')
                                    ->label('크론 표현식')
                                    ->copyable()
                                    ->helperText(fn () => $this->record->getHumanReadableExpression()),
                                    
                                TextEntry::make('timezone')
                                    ->label('시간대'),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('today_runs')
                                    ->label('오늘 실행 예정')
                                    ->getStateUsing(function () {
                                        $runs = $this->record->getTodayRuns();
                                        if (empty($runs)) {
                                            return '오늘은 실행 예정 없음';
                                        }
                                        return collect($runs)->map(fn ($run) => $run->format('H:i'))->join(', ');
                                    }),
                                    
                                TextEntry::make('tomorrow_runs')
                                    ->label('내일 실행 예정')
                                    ->getStateUsing(function () {
                                        $runs = $this->record->getTomorrowRuns();
                                        if (empty($runs)) {
                                            return '내일은 실행 예정 없음';
                                        }
                                        return collect($runs)->map(fn ($run) => $run->format('H:i'))->join(', ');
                                    }),
                            ]),
                            
                        KeyValueEntry::make('parameters')
                            ->label('매개변수')
                            ->default([]),
                    ]),
                    
                Section::make('실행 옵션')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label('활성화 상태')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle'),
                                    
                                IconEntry::make('without_overlapping')
                                    ->label('중복 실행 방지')
                                    ->boolean(),
                                    
                                IconEntry::make('run_in_background')
                                    ->label('백그라운드 실행')
                                    ->boolean(),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('max_runtime')
                                    ->label('최대 실행 시간')
                                    ->suffix(' 초')
                                    ->default('제한 없음'),
                                    
                                TextEntry::make('notification_email')
                                    ->label('알림 이메일')
                                    ->default('설정 안 됨'),
                            ]),
                    ]),
                    
                Section::make('모니터링 정보')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('monitor.is_healthy')
                                    ->label('건강 상태')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-exclamation-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->default(true),
                                    
                                TextEntry::make('monitor.health_check_message')
                                    ->label('상태 메시지')
                                    ->default('정상'),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('monitor.last_success_at')
                                    ->label('마지막 성공')
                                    ->dateTime('Y-m-d H:i:s')
                                    ->default('없음'),
                                    
                                TextEntry::make('monitor.last_failure_at')
                                    ->label('마지막 실패')
                                    ->dateTime('Y-m-d H:i:s')
                                    ->default('없음'),
                                    
                                TextEntry::make('monitor.current_consecutive_failures')
                                    ->label('연속 실패 횟수')
                                    ->suffix(' 회')
                                    ->default(0),
                            ]),
                    ]),
                    
                Section::make('시스템 정보')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('생성일시')
                                    ->dateTime('Y-m-d H:i:s'),
                                    
                                TextEntry::make('updated_at')
                                    ->label('수정일시')
                                    ->dateTime('Y-m-d H:i:s'),
                                    
                                IconEntry::make('is_system')
                                    ->label('시스템 작업')
                                    ->boolean(),
                            ]),
                    ]),
            ]);
    }
} 