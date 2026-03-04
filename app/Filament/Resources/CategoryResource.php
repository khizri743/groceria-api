<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn; // Added this
// use App\Filament\Resources\CategoryResource\Pages;
// use App\Models\Category;
use Filament\Forms;
// use Filament\Forms\Form;
// use Filament\Resources\Resource;
use Filament\Tables;
// use Filament\Tables\Table;
// use Filament\Forms\Components\TextInput;
// use Filament\Forms\Components\ColorPicker;
// use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Columns\ImageColumn;
// use Filament\Tables\Columns\ColorColumn;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                ColorPicker::make('bg_color')->label('Background Color'),
                TextInput::make('image_url')->label('Image URL'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->striped()
            ->columns([
                ImageColumn::make('image_url')->circular(),
                TextColumn::make('name')->sortable()->searchable(),
                ColorColumn::make('bg_color'),
            ])
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
