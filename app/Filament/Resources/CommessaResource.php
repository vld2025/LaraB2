<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommessaResource\Pages;
use App\Filament\Resources\CommessaResource\RelationManagers;
use App\Models\Commessa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommessaResource extends Resource
{
    protected static ?string $model = Commessa::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cantiere_id')
                    ->relationship('cantiere', 'id')
                    ->required(),
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('codice')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descrizione')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('data_inizio'),
                Forms\Components\DatePicker::make('data_fine'),
                Forms\Components\TextInput::make('budget')
                    ->numeric(),
                Forms\Components\Toggle::make('attiva')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cantiere.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_inizio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_fine')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('attiva')
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
            'index' => Pages\ListCommessas::route('/'),
            'create' => Pages\CreateCommessa::route('/create'),
            'edit' => Pages\EditCommessa::route('/{record}/edit'),
        ];
    }
}
