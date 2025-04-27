<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PushNotification;
use Filament\Resources\Resource;
use App\Channels\FirebaseChannel;
use Filament\Tables\Actions\Action;
use App\Models\UserPushNotification;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\SendPushNotification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PushNotificationResource\Pages;
use App\Filament\Resources\PushNotificationResource\RelationManagers;

class PushNotificationResource extends Resource
{
    protected static ?string $model = PushNotification::class;

    protected static ?string $navigationIcon = 'heroicon-s-bell-alert';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Select::make('target')
                    ->label('Target Audience')
                    ->options([
                        'all' => 'All Users',
                        'specific' => 'Specific Users',
                        "everyone" => "Everyone",
                    ])
                    ->reactive()
                    ->required(),
                TextInput::make('title')
                    ->label('Notification Title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('message')
                    ->label('Notification Message')
                    ->required()
                    ->columnSpanFull(),


                Select::make('user_ids')
                    ->label('Select Users')
                    ->multiple()
                    ->options(User::whereNotNull('firebase_token')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->hidden(fn($get) => $get('target') !== 'specific')
                    ->reactive(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('target')
                    ->searchable(),
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
                Action::make('send')
                    ->label('Send Notification')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (PushNotification $record) {
                        // Fetch users based on target type
                        if ($record->target === 'specific' || $record->target === 'all') {
                            $users = $record->target === 'all'
                                ? User::whereNotNull('firebase_token')->get()
                                : User::whereIn('id', $record->user_ids)->get();

                            foreach ($users as $user) {
                                $user->notify(new SendPushNotification($record->title, $record->message));

                                UserPushNotification::create([
                                    'user_id' => $user->id,
                                    'push_notification_id' => $record->id,
                                    'is_read' => false,
                                ]);
                            }
                            Notification::make()
                                ->title('Notification sent successfully')
                                ->success()
                                ->send();
                        } else if ($record->target === 'everyone') {
                            $firebaseChannel = new FirebaseChannel();
                            $res = $firebaseChannel->sendNotificationTopic('everyone', $record->title, $record->message);
                            Notification::make()
                                ->title('Notification sent successfully')
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(),
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
            'index' => Pages\ListPushNotifications::route('/'),
            'create' => Pages\CreatePushNotification::route('/create'),
            'edit' => Pages\EditPushNotification::route('/{record}/edit'),
        ];
    }
}
