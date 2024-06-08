<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $project = Project::find($this->record->project_id);

        $totalCostExcludePpn = $project->total_cost_exclude_ppn + $this->record->total_cost;

        $project->update([
            'total_cost_exclude_ppn' => $totalCostExcludePpn,
            'total_cost_rounded' => round($totalCostExcludePpn, -3),
        ]);
    }
}
