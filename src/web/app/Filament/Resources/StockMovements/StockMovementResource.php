<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\ManageStockMovements;
use App\Modules\Inventory\Models\StockMovement;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $slug = 'stock-movements';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog & Inventory';

    public static function getNavigationLabel(): string
    {
        return __('Stock Movements');
    }

    public static function getModelLabel(): string
    {
        return __('Stock Movements');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('warehouse_id')
                    ->label(__('Warehouse'))
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('product_id')
                    ->label(__('Product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('movement_type')
                    ->label(__('Movement Type'))
                    ->options([
                        'goods_receipt' => __('Goods Receipt (+)'),
                        'goods_issue' => __('Goods Issue (-)'),
                        'adjustment_in' => __('Adjustment In (+)'),
                        'adjustment_out' => __('Adjustment Out (-)'),
                        'transfer_in' => __('Transfer In (+)'),
                        'transfer_out' => __('Transfer Out (-)'),
                    ])
                    ->required(),
                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->required(),
                TextInput::make('unit_cost')
                    ->label(__('Unit Cost'))
                    ->numeric()
                    ->prefix('CHF')
                    ->default('0.0000'),
                TextInput::make('reference_type')
                    ->label(__('Reference Type'))
                    ->maxLength(50),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Date & Time'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label(__('Warehouse'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('Product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->label(__('Movement Type'))
                    ->badge()
                    ->colors([
                        'success' => fn ($state): bool => in_array($state, ['goods_receipt', 'adjustment_in', 'transfer_in'], true),
                        'danger' => fn ($state): bool => in_array($state, ['goods_issue', 'adjustment_out', 'transfer_out'], true),
                    ]),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label(__('Unit Cost'))
                    ->money('CHF')
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label(__('Reference'))
                    ->searchable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStockMovements::route('/'),
        ];
    }
}
