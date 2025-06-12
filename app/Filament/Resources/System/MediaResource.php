<?php

namespace App\Filament\Resources\System;

use App\Filament\Resources\System\MediaResource\Pages;
use App\Models\System\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class MediaResource extends Resource
{
        protected static ?string $model = Media::class;
    
    protected static ?string $slug = 'system/media';
    
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = '시스템';
    protected static ?string $navigationLabel = '미디어';
    protected static ?int $navigationSort = 30;
    
    protected static ?string $modelLabel = '미디어';
    protected static ?string $pluralModelLabel = '미디어 파일';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('미디어 정보')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('파일명')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('file_name')
                            ->label('실제 파일명')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('mime_type')
                            ->label('MIME 타입')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('size')
                            ->label('파일 크기')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => number_format($state / 1024 / 1024, 2) . ' MB'),
                            
                        Forms\Components\KeyValue::make('custom_properties')
                            ->label('커스텀 속성')
                            ->addButtonLabel('속성 추가'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('미리보기')
                    ->schema([
                        Forms\Components\ViewField::make('preview')
                            ->label('')
                            ->view('filament.forms.components.media-preview'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview_url')
                    ->label('미리보기')
                    ->width(80)
                    ->height(80)
                    ->defaultImageUrl(url('/images/placeholder.png')),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('파일명')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('타입')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match(true) {
                        str_starts_with($state, 'image/') => '이미지',
                        str_starts_with($state, 'video/') => '비디오',
                        str_starts_with($state, 'audio/') => '오디오',
                        $state === 'application/pdf' => 'PDF',
                        default => '문서'
                    })
                    ->color(fn ($state) => match(true) {
                        str_starts_with($state, 'image/') => 'success',
                        str_starts_with($state, 'video/') => 'info',
                        str_starts_with($state, 'audio/') => 'warning',
                        default => 'gray'
                    }),
                    
                Tables\Columns\TextColumn::make('human_readable_size')
                    ->label('크기')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('size', $direction);
                    }),
                    
                Tables\Columns\TextColumn::make('model_type')
                    ->label('연결된 모델')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('collection_name')
                    ->label('컬렉션')
                    ->badge()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('업로드일')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('파일 타입')
                    ->options([
                        'image' => '이미지',
                        'video' => '비디오',
                        'document' => '문서',
                        'other' => '기타',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match($data['value']) {
                            'image' => $query->where('mime_type', 'like', 'image/%'),
                            'video' => $query->where('mime_type', 'like', 'video/%'),
                            'document' => $query->whereIn('mime_type', [
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]),
                            'other' => $query->whereNotIn('mime_type', function ($q) {
                                $q->select('mime_type')
                                    ->from('media')
                                    ->where('mime_type', 'like', 'image/%')
                                    ->orWhere('mime_type', 'like', 'video/%')
                                    ->orWhereIn('mime_type', [
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ]);
                            }),
                            default => $query,
                        };
                    }),
                    
                Filter::make('large_files')
                    ->label('대용량 파일 (10MB 이상)')
                    ->query(fn (Builder $query): Builder => $query->where('size', '>=', 10 * 1024 * 1024)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('다운로드')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Media $record): string => $record->getUrl())
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
} 