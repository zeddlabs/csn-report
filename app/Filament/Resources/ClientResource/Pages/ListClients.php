<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->color('gray')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $clients = Client::all();

                    return response()->streamDownload(function () use ($clients) {
                        echo Pdf::loadView('reports.client', ['clients' => $clients])->setPaper('b4')->stream();
                    }, 'Client Report.pdf');
                }),
            Actions\CreateAction::make()
                ->label('Add Client'),
        ];
    }
}
