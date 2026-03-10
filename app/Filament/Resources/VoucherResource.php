<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Resources\VoucherResource\RelationManagers;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationLabel = 'Coupons & Vouchers';

    // Limit access to Admins only (Staff cannot create coupons by default to prevent fraud)
    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('vouchers', 'read');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('vouchers', 'write');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasPermission('vouchers', 'write');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasPermission('vouchers', 'write');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Coupon Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g. SUMMER20')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']), // Visual helper
                    
                TextInput::make('description')
                    ->label('Description')
                    ->placeholder('e.g. 20% off all summer fruits')
                    ->maxLength(255),

                Select::make('type')
                    ->label('Discount Type')
                    ->options([
                        'percent' => 'Percentage (%)',
                        'fixed' => 'Fixed Amount ($)',
                    ])
                    ->required(),

                TextInput::make('value')
                    ->label('Discount Value')
                    ->numeric()
                    ->required()
                    ->helperText('Enter the percentage (e.g. 20) or flat dollar amount (e.g. 5.00)'),

                DateTimePicker::make('expires_at')
                    ->label('Expiration Date & Time')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->weight('bold')
                    ->copyable() // Click to copy code
                    ->copyMessage('Coupon code copied!'),

                TextColumn::make('description')->limit(30),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percent' => 'info',
                        'fixed' => 'success',
                    }),

                TextColumn::make('value')
                    ->formatStateUsing(fn ($record) => $record->type === 'percent' ? $record->value . '%' : '$' . $record->value),

                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->expires_at < now() ? 'danger' : 'gray'), // Highlights expired coupons in red
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return[];
    }

    public static function getPages(): array
    {
        return[
            'index' => Pages\ListVouchers::route('/'),
            // 'create' => Pages\CreateVoucher::route('/create'),
            // 'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}

