<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Recall;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Tables\Actions\EditAction;
use App\Models\PushNotification;
use Filament\Resources\Resource;
use App\Channels\FirebaseChannel;
use Filament\Tables\Actions\Action;
use Tables\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\SendPushNotification;
use App\Filament\Resources\RecallResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RecallResource\RelationManagers;


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
                // Forms\Components\TextInput::make('image_url')
                //     ->label('Product image')
                //     ->required(),

                FileUpload::make('image_url')
                    ->avatar()
                    ->label('Product image'),
                Forms\Components\TextInput::make('recall_description')
                    ->label('Reason for recall')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([ 
                ImageColumn::make('image_url')
                    ->label('Product image')
                    ->circular(),
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
