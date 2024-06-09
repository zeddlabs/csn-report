<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectProgressResource\Pages;
use App\Filament\Resources\ProjectProgressResource\RelationManagers;
use App\Models\Client;
use App\Models\ProjectProgress;
use App\Models\WorkType;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectProgressResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ProjectProgress::class;

    protected static ?string $modelLabel = 'Project Progress';
    protected static ?string $pluralModelLabel = 'Project Progress';
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Project Progress';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'project-progress';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $client = Client::find($state);
                                $set('project_name', $client->project->name);
                            }),
                        Forms\Components\TextInput::make('project_name')
                            ->label('Project Name')
                            ->placeholder('Project Name')
                            ->readOnly()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Get $get, Set $set) {
                                if (!$get('client_id')) {
                                    return;
                                }

                                $client = Client::find($get('client_id'));
                                $set('project_name', $client->project->name);
                            }),
                    ]),
                Forms\Components\Section::make('Works Information')
                    ->schema([
                        Forms\Components\Repeater::make('works')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('work_type_id')
                                    ->relationship('workType', 'name')
                                    ->label('Work Type')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Repeater::make('materials')
                                    ->relationship()
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('unit')
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity_plan')
                                            ->label('Quantity (Plan)')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->required()
                                            ->live(debounce: 1000)
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $quantityProgress = $get('quantity_progress');

                                                if ((int)$quantityProgress === 0 || (int)$state === 0) {
                                                    $set('weight_factory', 0);
                                                    return;
                                                }

                                                $set('weight_factory', round(($quantityProgress / $state) * 100));
                                            }),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->required()
                                            ->live(debounce: 1000)
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $quantityProgress = $get('quantity_progress');
                                                $set('total_price', round($state * $quantityProgress, 2));
                                            }),
                                        Forms\Components\TextInput::make('quantity_progress')
                                            ->label('Quantity (Progress)')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->required()
                                            ->live(debounce: 1000)
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $unitPrice = $get('unit_price');
                                                $set('total_price', round($unitPrice * $state, 2));

                                                $quantityPlan = $get('quantity_plan');

                                                if ((int)$quantityPlan === 0 || (int)$state === 0) {
                                                    $set('weight_factory', 0);
                                                    return;
                                                }

                                                $set('weight_factory', round(($state / $quantityPlan) * 100));
                                            }),
                                        Forms\Components\TextInput::make('total_price')
                                            ->label('Total Price')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('weight_factory')
                                            ->label('Weight Factory')
                                            ->suffix('%')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly(),
                                    ]),
                                Forms\Components\FileUpload::make('attachment')
                                    ->image()
                            ])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $works = $get('works');

                                $totalProgress = collect($works)->flatMap(fn ($work) => collect($work['materials']))->sum('weight_factory');
                                $totalProgress = $totalProgress / collect($works)->flatMap(fn ($work) => collect($work['materials']))->count();

                                $constructionCost = collect($works)->flatMap(fn ($work) => collect($work['materials']))->sum('total_price');
                                $ppn = $constructionCost * 0.11;
                                $totalConstructionCost = $constructionCost + $ppn;

                                $set('total_progress', round($totalProgress, 2));
                                $set('construction_cost', round($constructionCost, 2));
                                $set('ppn', round($ppn, 2));
                                $set('total_construction_cost', round($totalConstructionCost, 2));
                            })
                            ->deleteAction(function (Action $action) {
                                return $action->after(function (Get $get, Set $set) {
                                    $works = $get('works');

                                    $totalProgress = collect($works)->flatMap(fn ($work) => collect($work['materials']))->sum('weight_factory');
                                    $totalProgress = $totalProgress / collect($works)->flatMap(fn ($work) => collect($work['materials']))->count();

                                    $constructionCost = collect($works)->flatMap(fn ($work) => collect($work['materials']))->sum('total_price');
                                    $ppn = $constructionCost * 0.11;
                                    $totalConstructionCost = $constructionCost + $ppn;

                                    $set('total_progress', round($totalProgress));
                                    $set('construction_cost', round($constructionCost, 2));
                                    $set('ppn', round($ppn, 2));
                                    $set('total_construction_cost', round($totalConstructionCost, 2));
                                });
                            }),
                    ]),
                Forms\Components\Section::make('Progress Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('total_progress')
                            ->label('Total Progress')
                            ->suffix('%')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                        Forms\Components\TextInput::make('construction_cost')
                            ->label('Construction Cost')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                        Forms\Components\TextInput::make('ppn')
                            ->label('PPN (11%)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_construction_cost')
                            ->label('Total Construction Cost')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                        Forms\Components\Toggle::make('is_approved')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->visible(fn () => auth()->user()->hasRole('super_admin')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('total_progress')
                    ->label('Total Progress')
                    ->suffix('%')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('construction_cost')
                    ->label('Construction Cost')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN (11%)')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_construction_cost')
                    ->label('Total Construction Cost')
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
                //
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->color('danger')
                    ->icon('heroicon-m-printer')
                    ->action(function (ProjectProgress $progress) {
                        $workTypes = WorkType::with(['works' => function ($query) use ($progress) {
                            $query->where('project_progress_id', $progress->id);
                        }])->get();

                        return response()->streamDownload(function () use ($progress, $workTypes) {
                            echo Pdf::loadView('reports.progress', [
                                'progress' => $progress,
                                'workTypes' => $workTypes,
                            ])->setPaper('b3')->stream();
                        }, 'BOQ Project Progress.pdf');
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
            'index' => Pages\ListProjectProgress::route('/'),
            'create' => Pages\CreateProjectProgress::route('/create'),
            'view' => Pages\ViewProjectProgress::route('/{record}'),
            'edit' => Pages\EditProjectProgress::route('/{record}/edit'),
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
