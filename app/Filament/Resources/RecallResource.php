<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Recall;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Tables\Actions\EditAction;
use Filament\Resources\Resource;
use Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RecallResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RecallResource\RelationManagers;
use Filament\Tables\Columns\ImageColumn;

class RecallResource extends Resource
{
    protected static ?string $model = Recall::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->label('product name')
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Product description')
                    ->required(),
                Forms\Components\TextInput::make('image_url')
                    ->label('Product image')
                    ->required(),
                Forms\Components\TextInput::make('recall_description')
                    ->label('Reason for recall')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([ 
                ImageColumn::make('image_url'),
                Tables\Columns\TextColumn::make('product_name')->label('Title')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('description')->sortable(),
                Tables\Columns\TextColumn::make('recall_description')->label('recall_reason')->sortable(),
            
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
            'index' => Pages\ListRecalls::route('/'),
            'create' => Pages\CreateRecall::route('/create'),
            'edit' => Pages\EditRecall::route('/{record}/edit'),
        ];
    }
}
