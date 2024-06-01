<?php

namespace App\Filament\Resources\ProjectProgressResource\Pages;

use App\Filament\Resources\ProjectProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectProgress extends EditRecord
{
    protected static string $resource = ProjectProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
