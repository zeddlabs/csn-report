<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
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
                    ]),
                Forms\Components\Section::make('Works Information')
                    ->relationship('projectProgress')
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
                                            ->required(),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('volume')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
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
