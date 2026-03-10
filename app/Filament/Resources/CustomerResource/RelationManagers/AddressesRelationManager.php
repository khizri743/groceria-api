<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('label')
                    ->required()
                    ->placeholder('e.g., Home, Office')
                    ->maxLength(255),
                TextInput::make('address_line')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('is_default')
                    ->label('Default Address'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label')->weight('bold'),
                Tables\Columns\TextColumn::make('address_line'),
                Tables\Columns\IconColumn::make('is_default')->boolean()->label('Default'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

// class AddressesRelationManager extends RelationManager
// {
//     protected static string $relationship = 'addresses';

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\TextInput::make('label')
//                     ->required()
//                     ->maxLength(255),
//             ]);
//     }

//     public function table(Table $table): Table
//     {
//         return $table
//             ->recordTitleAttribute('label')
//             ->columns([
//                 Tables\Columns\TextColumn::make('label'),
//             ])
//             ->filters([
//                 //
//             ])
//             ->headerActions([
//                 Tables\Actions\CreateAction::make(),
//             ])
//             ->actions([
//                 Tables\Actions\EditAction::make(),
//                 Tables\Actions\DeleteAction::make(),
//             ])
//             ->bulkActions([
//                 Tables\Actions\BulkActionGroup::make([
//                     Tables\Actions\DeleteBulkAction::make(),
//                 ]),
//             ]);
//     }
// }
