<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form->schema([]); // Read-only from this view
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('total_amount')->money('USD'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'pending'    => 'gray',
                        'cancelled'  => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_method')->label('Payment'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y h:i A')->label('Date'),
            ])
            ->filters([])
            ->headerActions([
                // Intentionally left empty. We don't want admins creating orders from here.
            ])
            ->actions([
                // We provide an action to jump to the main Order edit page
                Tables\Actions\Action::make('View Order')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.orders.edit', $record)),
            ]);
    }
}

// class OrdersRelationManager extends RelationManager
// {
//     protected static string $relationship = 'orders';

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\TextInput::make('order_number')
//                     ->required()
//                     ->maxLength(255),
//             ]);
//     }

//     public function table(Table $table): Table
//     {
//         return $table
//             ->recordTitleAttribute('order_number')
//             ->columns([
//                 Tables\Columns\TextColumn::make('order_number'),
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
