<?php

namespace App\Filament\Resources\System;

use App\Filament\Resources\System\ScheduledTaskResource\Pages;
use App\Filament\Resources\System\ScheduledTaskResource\RelationManagers;
use App\Models\System\ScheduledTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ScheduledTaskResource extends Resource
{
    protected static ?string $model = ScheduledTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationGroup = '시스템 관리';
    
    protected static ?string $navigationLabel = '스케줄 관리';
    
    protected static ?string $modelLabel = '스케줄 작업';
    
    protected static ?string $pluralModelLabel = '스케줄 작업';
    
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('기본 정보')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('작업 이름')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('예: 일일 백업 작업'),
                                    
                                Forms\Components\Select::make('type')
                                    ->label('작업 유형')
                                    ->options([
                                        'command' => 'Artisan 명령',
                                        'job' => 'Job 클래스',
                                        'closure' => '클로저 (시스템 전용)',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                        $state === 'closure' ? $set('is_system', true) : null
                                    ),
                            ]),
                            
                        Forms\Components\TextInput::make('command')
                            ->label(fn (Forms\Get $get) => match($get('type')) {
                                'command' => 'Artisan 명령어',
                                'job' => 'Job 클래스명',
                                'closure' => '실행할 코드',
                                default => '명령/클래스'
                            })
                            ->required()
                            ->placeholder(fn (Forms\Get $get) => match($get('type')) {
                                'command' => '예: backup:run --only-db',
                                'job' => '예: App\Jobs\ProcessReportJob',
                                'closure' => '예: Log::info("스케줄 실행됨");',
                                default => ''
                            })
                            ->helperText(fn (Forms\Get $get) => match($get('type')) {
                                'command' => 'php artisan 없이 명령어만 입력하세요',
                                'job' => '전체 네임스페이스를 포함한 클래스명을 입력하세요',
                                'closure' => '보안상 시스템 작업에서만 사용 가능합니다',
                                default => ''
                            }),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('설명')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('스케줄 설정')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('expression')
                                    ->label('크론 표현식')
                                    ->required()
                                    ->placeholder('예: 0 3 * * * (매일 새벽 3시)')
                                    ->helperText('분 시 일 월 요일 형식')
                                    ->rules(['regex:/^[\*\/\-\,0-9]+\s[\*\/\-\,0-9]+\s[\*\/\-\,0-9]+\s[\*\/\-\,0-9]+\s[\*\/\-\,0-9]+$/'])
                                    ->validationAttribute('크론 표현식'),
                                    
                                Forms\Components\Select::make('timezone')
                                    ->label('시간대')
                                    ->options([
                                        'Asia/Seoul' => '서울 (KST)',
                                        'UTC' => 'UTC',
                                        'America/New_York' => '뉴욕 (EST/EDT)',
                                        'Europe/London' => '런던 (GMT/BST)',
                                    ])
                                    ->default('Asia/Seoul')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Select::make('category')
                            ->label('카테고리')
                            ->options([
                                'backup' => '백업',
                                'maintenance' => '유지보수',
                                'report' => '리포트',
                                'sync' => '동기화',
                                'cleanup' => '정리',
                                'notification' => '알림',
                                'other' => '기타',
                            ])
                            ->placeholder('카테고리 선택'),
                            
                        Forms\Components\KeyValue::make('parameters')
                            ->label('매개변수')
                            ->keyLabel('키')
                            ->valueLabel('값')
                            ->addButtonLabel('매개변수 추가')
                            ->helperText('Job 클래스나 명령어에 전달할 매개변수'),
                    ]),
                    
                Section::make('실행 옵션')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('max_runtime')
                                    ->label('최대 실행 시간')
                                    ->numeric()
                                    ->suffix('초')
                                    ->placeholder('예: 3600 (1시간)')
                                    ->helperText('비워두면 기본값 사용'),
                                    
                                Forms\Components\TextInput::make('notification_email')
                                    ->label('알림 이메일')
                                    ->email()
                                    ->placeholder('admin@example.com')
                                    ->helperText('실행 결과를 받을 이메일 주소'),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('활성화')
                                    ->default(true)
                                    ->helperText('비활성화 시 실행되지 않음'),
                                    
                                Forms\Components\Toggle::make('without_overlapping')
                                    ->label('중복 실행 방지')
                                    ->default(true)
                                    ->helperText('이전 실행이 끝나지 않으면 대기'),
                                    
                                Forms\Components\Toggle::make('run_in_background')
                                    ->label('백그라운드 실행')
                                    ->default(false)
                                    ->helperText('큐를 통해 비동기 실행'),
                            ]),
                            
                        Forms\Components\Toggle::make('is_system')
                            ->label('시스템 작업')
                            ->disabled()
                            ->helperText('시스템 작업은 삭제할 수 없습니다')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'closure'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('작업 이름')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('유형')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'command' => 'info',
                        'job' => 'success',
                        'closure' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'command' => 'Command',
                        'job' => 'Job',
                        'closure' => 'Closure',
                    }),
                    
                Tables\Columns\TextColumn::make('expression')
                    ->label('스케줄')
                    ->formatStateUsing(fn (ScheduledTask $record): string => 
                        $record->getHumanReadableExpression()
                    )
                    ->description(fn (ScheduledTask $record): string => 
                        $record->expression
                    ),
                    
                Tables\Columns\TextColumn::make('category')
                    ->label('카테고리')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'backup' => '백업',
                        'maintenance' => '유지보수',
                        'report' => '리포트',
                        'sync' => '동기화',
                        'cleanup' => '정리',
                        'notification' => '알림',
                        'other' => '기타',
                        default => '-',
                    }),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('활성화')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('next_run_at')
                    ->label('다음 실행')
                    ->getStateUsing(fn (ScheduledTask $record): ?string => 
                        $record->getNextRunTime()?->diffForHumans()
                    )
                    ->description(fn (ScheduledTask $record): ?string => 
                        $record->getNextRunTime()?->format('Y-m-d H:i:s')
                    )
                    ->color(fn (ScheduledTask $record): string => 
                        $record->is_active ? 'success' : 'gray'
                    ),
                    
                Tables\Columns\TextColumn::make('monitor.is_healthy')
                    ->label('상태')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => 
                        $state ? '정상' : '비정상'
                    )
                    ->color(fn ($state): string => 
                        $state ? 'success' : 'danger'
                    )
                    ->default('정상'),
                    
                Tables\Columns\TextColumn::make('latestLog.status')
                    ->label('최근 실행')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        'pending' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'success' => '성공',
                        'failed' => '실패',
                        'running' => '실행중',
                        'pending' => '대기중',
                        default => '-',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('활성화 상태')
                    ->placeholder('전체')
                    ->trueLabel('활성화')
                    ->falseLabel('비활성화'),
                    
                SelectFilter::make('type')
                    ->label('작업 유형')
                    ->options([
                        'command' => 'Command',
                        'job' => 'Job',
                        'closure' => 'Closure',
                    ]),
                    
                SelectFilter::make('category')
                    ->label('카테고리')
                    ->options([
                        'backup' => '백업',
                        'maintenance' => '유지보수',
                        'report' => '리포트',
                        'sync' => '동기화',
                        'cleanup' => '정리',
                        'notification' => '알림',
                        'other' => '기타',
                    ]),
                    
                SelectFilter::make('health')
                    ->label('건강 상태')
                    ->relationship('monitor', 'is_healthy')
                    ->options([
                        1 => '정상',
                        0 => '비정상',
                    ]),
            ])
            ->actions([
                Action::make('toggle')
                    ->label(fn (ScheduledTask $record): string => 
                        $record->is_active ? '비활성화' : '활성화'
                    )
                    ->icon(fn (ScheduledTask $record): string => 
                        $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play'
                    )
                    ->color(fn (ScheduledTask $record): string => 
                        $record->is_active ? 'warning' : 'success'
                    )
                    ->requiresConfirmation()
                    ->modalHeading(fn (ScheduledTask $record): string => 
                        $record->is_active ? '작업 비활성화' : '작업 활성화'
                    )
                    ->modalDescription(fn (ScheduledTask $record): string => 
                        $record->is_active 
                            ? '이 작업을 비활성화하시겠습니까? 스케줄에 따른 자동 실행이 중지됩니다.'
                            : '이 작업을 활성화하시겠습니까? 스케줄에 따라 자동으로 실행됩니다.'
                    )
                    ->action(function (ScheduledTask $record): void {
                        $isActive = $record->toggleActive();
                        
                        Notification::make()
                            ->title($isActive ? '작업이 활성화되었습니다' : '작업이 비활성화되었습니다')
                            ->success()
                            ->send();
                    }),
                    
                Action::make('run')
                    ->label('즉시 실행')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('작업 즉시 실행')
                    ->modalDescription('이 작업을 지금 즉시 실행하시겠습니까?')
                    ->action(function (ScheduledTask $record): void {
                        $log = $record->runNow('manual', auth()->id());
                        
                        Notification::make()
                            ->title('작업이 시작되었습니다')
                            ->body("작업 '{$record->name}'이(가) 실행 대기열에 추가되었습니다.")
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->label('선택 항목 활성화')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $count = 0;
                        foreach ($records as $record) {
                            if (!$record->is_active) {
                                $record->update(['is_active' => true]);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("{$count}개 작업이 활성화되었습니다")
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\BulkAction::make('deactivate')
                    ->label('선택 항목 비활성화')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->is_active) {
                                $record->update(['is_active' => false]);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("{$count}개 작업이 비활성화되었습니다")
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records): void {
                            foreach ($records as $record) {
                                if ($record->is_system) {
                                    Notification::make()
                                        ->title('시스템 작업은 삭제할 수 없습니다')
                                        ->danger()
                                        ->send();
                                    
                                    throw new \Exception('시스템 작업은 삭제할 수 없습니다');
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduledTasks::route('/'),
            'create' => Pages\CreateScheduledTask::route('/create'),
            'view' => Pages\ViewScheduledTask::route('/{record}'),
            'edit' => Pages\EditScheduledTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
} 