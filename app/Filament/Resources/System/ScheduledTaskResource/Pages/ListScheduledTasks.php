<?php

namespace App\Filament\Resources\System\ScheduledTaskResource\Pages;

use App\Filament\Resources\System\ScheduledTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListScheduledTasks extends ListRecords
{
    protected static string $resource = ScheduledTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            '전체' => Tab::make(),
            '활성화' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getModel()::where('is_active', true)->count()),
            '비활성화' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => $this->getModel()::where('is_active', false)->count()),
            '비정상' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('monitor', fn ($q) => $q->where('is_healthy', false)))
                ->badge(fn () => $this->getModel()::whereHas('monitor', fn ($q) => $q->where('is_healthy', false))->count())
                ->badgeColor('danger'),
        ];
    }
} 