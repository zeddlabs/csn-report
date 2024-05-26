<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Js;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->color('gray')
                ->icon('heroicon-o-printer')
                ->action(function (Report $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('bq-report', ['record' => $record])->setPaper('b4')->stream();
                    }, 'BQ Project Report.pdf');
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
