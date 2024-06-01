<?php

namespace App\Filament\Resources\ProjectProgressResource\Pages;

use App\Filament\Resources\ProjectProgressResource;
use App\Models\ProjectProgress;
use App\Models\Work;
use App\Models\WorkType;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

use function Laravel\Prompts\progress;

class ViewProjectProgress extends ViewRecord
{
    protected static string $resource = ProjectProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->color('gray')
                ->icon('heroicon-o-printer')
                ->action(function (ProjectProgress $progress) {
                    $workTypes = WorkType::with(['works' => function ($query) use ($progress) {
                        $query->where('project_progress_id', $progress->id);
                    }])->get();
                    // dd($workTypes[0]->works);

                    return response()->streamDownload(function () use ($progress, $workTypes) {
                        echo Pdf::loadView('reports.progress', [
                            'progress' => $progress,
                            'workTypes' => $workTypes,
                        ])->setPaper('b3')->stream();
                    }, 'BOQ Project Progress.pdf');
                }),
            Actions\EditAction::make(),
        ];
    }
}
