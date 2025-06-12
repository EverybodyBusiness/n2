<?php

namespace App\Filament\Resources\System;

use App\Filament\Resources\System\AuditResource\Pages;
use App\Models\System\Audit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;
    
    protected static ?string $slug = 'system/audits';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = '감사 로그';

    protected static ?string $navigationGroup = '시스템';

    protected static ?string $label = '감사 로그';

    protected static ?string $pluralLabel = '감사 로그';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('감사 정보')
                    ->schema([
                        Forms\Components\TextInput::make('auditable_type')
                            ->label('대상 모델')
                            ->disabled(),

                        Forms\Components\TextInput::make('auditable_id')
                            ->label('대상 ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('event')
                            ->label('이벤트')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('수행자')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('수행 일시')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('변경 내용')
                    ->schema([
                        Forms\Components\KeyValue::make('old_values')
                            ->label('이전 값')
                            ->disabled(),

                        Forms\Components\KeyValue::make('new_values')
                            ->label('새로운 값')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('기타 정보')
                    ->schema([
                        Forms\Components\TextInput::make('user_agent')
                            ->label('사용자 에이전트')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP 주소')
                            ->disabled(),

                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('auditable_type')
                    ->label('대상 모델')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge(),

                TextColumn::make('auditable_id')
                    ->label('대상 ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_name')
                    ->label('이벤트')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '생성' => 'success',
                        '수정' => 'warning',
                        '삭제' => 'danger',
                        '복구' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('user.name')
                    ->label('수행자')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP 주소')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('수행 일시')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('auditable_type')
                    ->label('대상 모델')
                    ->options(function () {
                        return Audit::query()
                            ->distinct()
                            ->pluck('auditable_type')
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                            ->toArray();
                    }),

                SelectFilter::make('event')
                    ->label('이벤트')
                    ->options([
                        'created' => '생성',
                        'updated' => '수정',
                        'deleted' => '삭제',
                        'restored' => '복구',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('시작일'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('종료일'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // 감사 로그는 삭제할 수 없음
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAudits::route('/'),
            'view' => Pages\ViewAudit::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // 감사 로그는 수동으로 생성할 수 없음
    }

    public static function canEdit($record): bool
    {
        return false; // 감사 로그는 수정할 수 없음
    }

    public static function canDelete($record): bool
    {
        return false; // 감사 로그는 삭제할 수 없음
    }
} 