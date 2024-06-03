<?php

namespace App\Filament\Widgets;

use App\Models\ProjectProgress;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LatestProjectProgress extends BaseWidget
{
    protected function getTableQuery(): Builder|Relation|null
    {
        return ProjectProgress::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('client.name')
                ->label('Client')
                ->searchable()
                ->wrap(),
            Tables\Columns\TextColumn::make('total_progress')
                ->label('Total Progress')
                ->suffix('%')
                ->numeric()
                ->sortable(),
        ];
    }
}
