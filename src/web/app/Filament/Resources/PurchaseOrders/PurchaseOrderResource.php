<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseOrders;

use App\Filament\Resources\PurchaseOrders\Pages\ManagePurchaseOrders;
use App\Modules\Purchasing\Models\PurchaseOrder;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $slug = 'purchase-orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Purchases & Vendors';

    public static function getNavigationLabel(): string
    {
        return __('Purchase Orders');
    }

    public static function getModelLabel(): string
    {
        return __('Purchase Orders');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label(__('Order Number'))
                    ->disabled()
                    ->dehydrated(false),
                Select::make('supplier_id')
                    ->label(__('Supplier'))
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('order_date')
                    ->label(__('Order Date'))
                    ->default(now())
                    ->required(),
                DatePicker::make('expected_date')
                    ->label(__('Expected Date'))
                    ->default(now()->addDays(14)),
                TextInput::make('currency')
                    ->label(__('Currency'))
                    ->default('CHF')
                    ->maxLength(3),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'ordered' => __('Ordered'),
                        'partially_received' => __('Partially Received'),
                        'received' => __('Received'),
                        'cancelled' => __('Cancelled'),
                    ])
                    ->default('draft')
                    ->required(),
                TextInput::make('subtotal')
                    ->label(__('Subtotal'))
                    ->numeric()
                    ->prefix('CHF')
                    ->default('0.00'),
                TextInput::make('tax_total')
                    ->label(__('VAT Total'))
                    ->numeric()
                    ->prefix('CHF')
                    ->default('0.00'),
                TextInput::make('total')
                    ->label(__('Total'))
                    ->numeric()
                    ->prefix('CHF')
                    ->default('0.00'),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('Order Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label(__('Supplier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label(__('Order Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_date')
                    ->label(__('Expected Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'ordered',
                        'warning' => 'partially_received',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('CHF')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ManagePurchaseOrders::route('/'),
        ];
    }
}
