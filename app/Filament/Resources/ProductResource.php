<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-m-table-cells';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                ->label('User')
                ->relationship('user', 'name') // Adjust 'name' to the field you want displayed
                ->required()
                ->options(\App\Models\User::pluck('name', 'id')->toArray())
                ->searchable()
                ->placeholder('Select a User'),

            Forms\Components\Toggle::make('archived')
                ->label('Archived')
                ->default(false),

            Forms\Components\Toggle::make('favorite')
                ->label('Favorite')
                ->default(false),

            Forms\Components\TextInput::make('code')
                ->label('Product Code')
                ->required(),

            Forms\Components\TextInput::make('barcode_number')
                ->label('Barcode Number')
                ->required(),

            Forms\Components\Textarea::make('barcode_formats')
                ->label('Barcode Formats')
                ->nullable(),

            Forms\Components\TextInput::make('mpn')
                ->label('MPN')
                ->nullable(),

            Forms\Components\TextInput::make('model')
                ->label('Model')
                ->nullable(),

            Forms\Components\TextInput::make('asin')
                ->label('ASIN')
                ->nullable(),

            Forms\Components\TextInput::make('title')
                ->label('Title')
                ->required(),

            Forms\Components\TextInput::make('category')
                ->label('Category')
                ->required(),

            Forms\Components\TextInput::make('manufacturer')
                ->label('Manufacturer')
                ->required(),

            Forms\Components\TextInput::make('serial_number')
                ->label('Serial Number')
                ->nullable(),

            Forms\Components\TextInput::make('weight')
                ->label('Weight')
                ->nullable(),

            Forms\Components\TextInput::make('dimension')
                ->label('Dimensions')
                ->nullable(),

            Forms\Components\TextInput::make('warranty_length')
                ->label('Warranty Length')
                ->nullable(),

            Forms\Components\TextInput::make('brand')
                ->label('Brand')
                ->required(),

            Forms\Components\Textarea::make('ingredients')
                ->label('Ingredients')
                ->nullable(),

            Forms\Components\Textarea::make('nutrition_facts')
                ->label('Nutrition Facts')
                ->nullable(),

            Forms\Components\TextInput::make('size')
                ->label('Size')
                ->nullable(),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->nullable(),

            Forms\Components\DateTimePicker::make('last_update')
                ->label('Last Update')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_id')->label('User ID')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                ->label('User Name') // Display the name of the user
                ->sortable()
                ->searchable(),
                Tables\Columns\IconColumn::make('archived')->label('Archived')->boolean()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('favorite')->label('Favorite')->boolean()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('title')->label('Title')->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Category')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('manufacturer')->label('Manufacturer')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('brand')->label('Brand')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_update')->label('Last Updated')->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
