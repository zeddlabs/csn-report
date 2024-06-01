<?php

namespace App\Filament\Resources\ProjectProgressResource\Pages;

use App\Filament\Resources\ProjectProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProjectProgress extends ViewRecord
{
    protected static string $resource = ProjectProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
