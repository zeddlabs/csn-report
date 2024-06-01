<?php

namespace App\Filament\Resources\ProjectProgressResource\Pages;

use App\Filament\Resources\ProjectProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectProgress extends ListRecords
{
    protected static string $resource = ProjectProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
