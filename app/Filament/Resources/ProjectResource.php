<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Project;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProjectResource\RelationManagers;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ProjectResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('name')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('area')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_approved')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->visible(fn () => auth()->user()->hasRole('super_admin')),
                    ]),
                Forms\Components\Section::make('Project Clients Information')
                    ->schema([
                        Forms\Components\Repeater::make('clients')
                            ->relationship()
                            ->columns(4)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('unit')
                                    ->required(),
                                Forms\Components\TextInput::make('volume')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $unitPrice = $get('unit_price');
                                        $set('total_cost', $unitPrice * $state);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $volume = $get('volume');
                                        $set('total_cost', $state * $volume);
                                    }),
                                Forms\Components\TextInput::make('total_cost')
                                    ->label('Total Cost')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly(),
                            ])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $clients = $get('clients');
                                $totalCostExcludePpn = collect($clients)->sum('total_cost');
                                $totalCostRounded = round($totalCostExcludePpn, -3);
                                $set('total_cost_exclude_ppn', $totalCostExcludePpn);
                                $set('total_cost_rounded', $totalCostRounded);
                            })
                            ->deleteAction(function (Action $action) {
                                return $action->after(function (Get $get, Set $set) {
                                    $clients = $get('clients');
                                    $totalCostExcludePpn = collect($clients)->sum('total_cost');
                                    $totalCostRounded = round($totalCostExcludePpn, -3);
                                    $set('total_cost_exclude_ppn', $totalCostExcludePpn);
                                    $set('total_cost_rounded', $totalCostRounded);
                                });
                            }),
                    ]),

                Forms\Components\Section::make('Project Cost Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('total_cost_exclude_ppn')
                            ->label('Total Cost (Exclude PPN)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_cost_rounded')
                            ->label('Total Cost (Rounded)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('area')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_cost_exclude_ppn')
                    ->label('Total Cost (Exclude PPN)')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost_rounded')
                    ->label('Total Cost (Rounded)')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Approved')
                    ->icon(fn (bool $state): string => match ($state) {
                        false => 'heroicon-o-clock',
                        true => 'heroicon-o-check-circle',
                    })
                    ->color(fn (bool $state): string => match ($state) {
                        false => 'warning',
                        true => 'success',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->color('danger')
                    ->icon('heroicon-m-printer')
                    ->action(function ($record) {
                        return response()->streamDownload(function () use ($record) {
                            echo Pdf::loadView('reports.client', ['record' => $record])->setPaper('b4')->stream();
                        }, 'Client Report.pdf');
                    })
                    ->visible(fn ($record) => $record->is_approved),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
