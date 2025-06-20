<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CantiereResource\Pages;
use App\Filament\Resources\CantiereResource\RelationManagers;
use App\Models\Cantiere;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CantiereResource extends Resource
{
    protected static ?string $model = Cantiere::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cliente_id')
                    ->relationship('cliente', 'id')
                    ->required(),
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('codice')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('indirizzo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cap')
                    ->maxLength(10),
                Forms\Components\TextInput::make('citta')
                    ->maxLength(255),
                Forms\Components\TextInput::make('provincia')
                    ->maxLength(2),
                Forms\Components\TextInput::make('nazione')
                    ->required()
                    ->maxLength(2)
                    ->default('CH'),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('attivo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('indirizzo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('citta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provincia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nazione')
                    ->searchable(),
                Tables\Columns\IconColumn::make('attivo')
                    ->boolean(),
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
            'index' => Pages\ListCantieres::route('/'),
            'create' => Pages\CreateCantiere::route('/create'),
            'edit' => Pages\EditCantiere::route('/{record}/edit'),
        ];
    }
}
