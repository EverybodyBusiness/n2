<?php

namespace App\Filament\Resources\System\ScheduledTaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Support\Enums\MaxWidth;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = '실행 로그';

    protected static ?string $recordTitleAttribute = 'started_at';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('started_at')
                    ->label('시작 시간')
                    ->required(),
                    
                Forms\Components\DateTimePicker::make('finished_at')
                    ->label('종료 시간'),
                    
                Forms\Components\Select::make('status')
                    ->label('상태')
                    ->options([
                        'pending' => '대기중',
                        'running' => '실행중',
                        'success' => '성공',
                        'failed' => '실패',
                        'timeout' => '시간초과',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('started_at')
            ->columns([
                Tables\Columns\TextColumn::make('started_at')
                    ->label('시작 시간')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('finished_at')
                    ->label('종료 시간')
                    ->dateTime('Y-m-d H:i:s')
                    ->default('-'),
                    
                Tables\Columns\TextColumn::make('duration')
                    ->label('실행 시간')
                    ->formatStateUsing(fn (?int $state): string => 
                        $state ? ($state >= 60 ? round($state / 60, 1) . '분' : $state . '초') : '-'
                    ),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        'pending' => 'gray',
                        'timeout' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => '성공',
                        'failed' => '실패',
                        'running' => '실행중',
                        'pending' => '대기중',
                        'timeout' => '시간초과',
                    }),
                    
                Tables\Columns\TextColumn::make('triggered_by')
                    ->label('실행 방식')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'schedule' => 'info',
                        'manual' => 'success',
                        'api' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'schedule' => '스케줄',
                        'manual' => '수동',
                        'api' => 'API',
                        default => '-',
                    }),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('실행자')
                    ->default('-')
                    ->visible(fn (): bool => 
                        $this->getOwnerRecord()->logs()->whereNotNull('user_id')->exists()
                    ),
                    
                Tables\Columns\TextColumn::make('memory_usage')
                    ->label('메모리 사용량')
                    ->formatStateUsing(fn (?int $state): string => 
                        $state ? number_format($state / 1024 / 1024, 2) . ' MB' : '-'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('has_error')
                    ->label('오류')
                    ->getStateUsing(fn ($record): bool => 
                        !empty($record->error_message)
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('상태')
                    ->options([
                        'success' => '성공',
                        'failed' => '실패',
                        'running' => '실행중',
                        'pending' => '대기중',
                        'timeout' => '시간초과',
                    ]),
                    
                SelectFilter::make('triggered_by')
                    ->label('실행 방식')
                    ->options([
                        'schedule' => '스케줄',
                        'manual' => '수동',
                        'api' => 'API',
                    ]),
                    
                Tables\Filters\Filter::make('has_error')
                    ->label('오류 있음')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('error_message')
                    ),
                    
                Tables\Filters\Filter::make('recent')
                    ->label('최근 7일')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('started_at', '>=', now()->subDays(7))
                    )
                    ->default(),
            ])
            ->headerActions([
                // 헤더 액션 없음
            ])
            ->actions([
                Action::make('view_details')
                    ->label('상세 보기')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => '실행 로그 상세 - ' . $record->started_at->format('Y-m-d H:i:s'))
                    ->modalContent(fn ($record) => view('filament.resources.scheduled-task.log-details', [
                        'log' => $record
                    ]))
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('선택 항목 삭제'),
                ]),
            ])
            ->defaultSort('started_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
} 