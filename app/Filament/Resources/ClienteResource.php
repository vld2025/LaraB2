<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ClienteResource extends Resource
{

    public static function canAccess(): bool
    {
        $user = auth()->user();
        // User normali possono solo visualizzare, admin/manager possono gestire
        return $user && ($user->hasRole(['admin', 'manager']) || $user->hasRole('user'));
    }
    protected static ?string $model = Cliente::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole(['admin', 'manager']);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['admin', 'manager']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.client_information'))
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label(__('app.company_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codice_cliente')
                            ->label(__('app.client_code'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\TextInput::make('piva_cf')
                            ->label(__('app.vat_number'))
                            ->maxLength(50),
                        Forms\Components\Toggle::make('attivo')
                            ->label(__('app.active'))
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.contacts'))
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label(__('app.email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefono')
                            ->label(__('app.phone'))
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('referente')
                            ->label(__('app.contact_person'))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.address'))
                    ->schema([
                        Forms\Components\TextInput::make('indirizzo')
                            ->label(__('app.street'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cap')
                            ->label(__('app.postal_code'))
                            ->maxLength(10),
                        Forms\Components\TextInput::make('citta')
                            ->label(__('app.city'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provincia')
                            ->label(__('app.province'))
                            ->maxLength(2),
                        Forms\Components\TextInput::make('paese')
                            ->label(__('app.country'))
                            ->default('Svizzera')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.notes'))
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label(__('app.notes'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label(__('app.company_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codice_cliente')
                    ->label(__('app.client_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('app.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label(__('app.phone')),
                Tables\Columns\TextColumn::make('referente')
                    ->label(__('app.contact_person'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('attivo')
                    ->label(__('app.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('app.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('attivo')
                    ->label(__('app.active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome');
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
            'index' => Pages\ListClienti::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.clients_orders');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.clients');
    }

    public static function getModelLabel(): string
    {
        return __('app.client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.clients');
    }
}
