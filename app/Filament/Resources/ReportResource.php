<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\RelationManagers;
use App\Models\Report;
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

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'QA/QC Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('activity_name')
                            ->label('Activity Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('area')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('works_title')
                            ->label('Works Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('works')
                            ->relationship()
                            ->columns(4)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('unit')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('volume')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $unitPrice = $get('unit_price');
                                        $set('total_price', $unitPrice * $state);
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
                                        $set('total_price', $state * $volume);
                                    }),
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly(),
                            ])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $works = $get('works');
                                $totalCostExcludePpn = collect($works)->sum('total_price');
                                $totalCostRounded = round($totalCostExcludePpn, -3);
                                $set('total_cost_exclude_ppn', $totalCostExcludePpn);
                                $set('total_cost_rounded', $totalCostRounded);
                            })
                            ->deleteAction(function (Action $action) {
                                return $action->after(function (Get $get, Set $set) {
                                    $works = $get('works');
                                    $totalCostExcludePpn = collect($works)->sum('total_price');
                                    $totalCostRounded = round($totalCostExcludePpn, -3);
                                    $set('total_cost_exclude_ppn', $totalCostExcludePpn);
                                    $set('total_cost_rounded', $totalCostRounded);
                                });
                            }),
                    ]),

                Forms\Components\Section::make('Costs')
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
                Tables\Columns\TextColumn::make('activity_name')
                    ->label('Activity Name')
                    ->searchable(),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
