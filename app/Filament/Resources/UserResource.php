<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Staff Management';
    protected static ?string $navigationGroup = 'Account';

    // Only Admins can see this Menu Item
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereIn('role', ['admin', 'staff']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Staff Details')->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create'),
                    
                    Select::make('role')
                        ->options([
                            'admin' => 'Super Admin',
                            'staff' => 'Staff',
                        ])
                        ->default('staff')
                        ->required()
                        ->live(), // <--- 1. Makes the dropdown trigger a live UI update
                ]),

                Section::make('Access Permissions')
                    ->description('Define what this user can do.')
                    // 👇 2. Hide this entire section if the role is 'admin'
                    ->hidden(fn (Forms\Get $get): bool => $get('role') === 'admin') 
                    ->schema([
                        Select::make('permissions.categories')
                            ->label('Categories Module')
                            ->options([
                                'none' => 'No Access',
                                'read' => 'Read Only',
                                'write' => 'Full Access',
                            ])->default('none'),

                        Select::make('permissions.products')
                            ->label('Products Module')
                            ->options([
                                'none' => 'No Access',
                                'read' => 'Read Only',
                                'write' => 'Full Access',
                            ])->default('none'),

                        Select::make('permissions.orders')
                            ->label('Orders Module')
                            ->options([
                                'none' => 'No Access',
                                'read' => 'Read Only',
                                'write' => 'Full Access',
                            ])->default('none'),

                        // 👇 3. NEW: Customers Module
                        Select::make('permissions.customers')
                            ->label('Customers Module')
                            ->options([
                                'none' => 'No Access',
                                'read' => 'Read Only',
                                'write' => 'Full Access',
                            ])->default('none'),

                        // 👇 4. NEW: Vouchers Module
                        Select::make('permissions.vouchers')
                            ->label('Coupons & Vouchers Module')
                            ->options([
                                'none' => 'No Access',
                                'read' => 'Read Only',
                                'write' => 'Full Access',
                            ])->default('none'),

                    ])->columns(2) // Makes the dropdowns sit side-by-side
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'staff' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(['admin'=>'Admin', 'staff'=>'Staff'])
            ]);
    }

    public static function getRelations(): array
    {
        return[];
    }

    public static function getPages(): array
    {
        return[
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}