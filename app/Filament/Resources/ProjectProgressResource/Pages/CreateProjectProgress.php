<?php

namespace App\Filament\Resources\ProjectProgressResource\Pages;

use App\Filament\Resources\ProjectProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectProgress extends CreateRecord
{
    protected static string $resource = ProjectProgressResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
