<?php

namespace App\Filament\Resources\System;

use App\Filament\Resources\System\TelescopeEntryResource\Pages;
use App\Models\System\TelescopeEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TelescopeEntryResource extends Resource
{
    protected static ?string $model = TelescopeEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?string $navigationLabel = 'Error Logs';
    
    protected static ?string $modelLabel = 'Error Log';
    
    protected static ?string $pluralModelLabel = 'Error Logs';
    
    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->disabled(),
                    
                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->disabled(),
                    
                Forms\Components\TextInput::make('user_id')
                    ->label('User ID')
                    ->disabled(),
                    
                Forms\Components\Textarea::make('content')
                    ->label('Content')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'exception' => 'danger',
                        'request' => 'info',
                        'query' => 'warning',
                        'job' => 'success',
                        'log' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('exception_class')
                    ->label('Exception')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record?->exception_class)
                    ->visible(fn ($record) => $record && $record->type === 'exception'),
                    
                Tables\Columns\TextColumn::make('exception_message')
                    ->label('Message')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record?->exception_message)
                    ->visible(fn ($record) => $record && $record->type === 'exception'),
                    
                Tables\Columns\TextColumn::make('request_method')
                    ->label('Method')
                    ->badge()
                    ->visible(fn ($record) => $record && $record->type === 'request'),
                    
                Tables\Columns\TextColumn::make('request_uri')
                    ->label('URI')
                    ->limit(30)
                    ->visible(fn ($record) => $record && $record->type === 'request'),
                    
                Tables\Columns\TextColumn::make('response_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        $state >= 300 => 'info',
                        $state >= 200 => 'success',
                        default => 'gray',
                    })
                    ->visible(fn ($record) => $record && $record->type === 'request'),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'exception' => 'Exception',
                        'request' => 'Request',
                        'query' => 'Query',
                        'job' => 'Job',
                        'log' => 'Log',
                    ]),
                    
                Tables\Filters\Filter::make('errors_only')
                    ->label('Errors Only')
                    ->query(fn (Builder $query): Builder => $query->where('type', 'exception'))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
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
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => $record ? url('/telescope/exceptions/' . $record->uuid) : '#')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // Telescope entries should not be deleted through Filament
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
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
            'index' => Pages\ListTelescopeEntries::route('/'),
            'view' => Pages\ViewTelescopeEntry::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Telescope entries are created automatically
    }

    public static function canEdit($record): bool
    {
        return false; // Telescope entries should not be edited
    }

    public static function canDelete($record): bool
    {
        return false; // Telescope entries should not be deleted through Filament
    }
}
