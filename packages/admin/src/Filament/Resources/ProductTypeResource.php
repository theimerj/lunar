<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages;
use Lunar\Admin\Support\Forms\Components\AttributeSelector;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\ProductType;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductTypeResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductType::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';  // TODO: remove me in Filament 3.1

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return __('lunarpanel::producttype.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::producttype.plural_label');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema(
                    static::getMainFormComponents()
                ),
                Forms\Components\Tabs::make('Attributes')->tabs([
                    Forms\Components\Tabs\Tab::make('Product Attributes')
                        ->schema([
                            AttributeSelector::make('mappedAttributes')
                                ->withType(Product::class)
                                ->relationship(name: 'mappedAttributes')
                                ->label('')
                                ->columnSpan(2),
                        ]),
                    Forms\Components\Tabs\Tab::make('Variant Attributes')
                        ->schema([
                            AttributeSelector::make('mappedAttributes')
                                ->withType(ProductVariant::class)
                                ->relationship(name: 'mappedAttributes')
                                ->label('')
                                ->columnSpan(2),
                        ]),

                ])->columnSpan(2),
            ]);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::producttype.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
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

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::producttype.table.name.label')),
        ];
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
            'index' => Pages\ListProductTypes::route('/'),
            'create' => Pages\CreateProductType::route('/create'),
            'edit' => Pages\EditProductType::route('/{record}/edit'),
        ];
    }
}
