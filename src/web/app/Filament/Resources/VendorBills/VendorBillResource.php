<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorBills;

use App\Filament\Resources\VendorBills\Pages\ManageVendorBills;
use App\Modules\Purchasing\Models\SupplierInvoice;
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

class VendorBillResource extends Resource
{
    protected static ?string $model = SupplierInvoice::class;

    protected static ?string $slug = 'vendor-bills';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Purchases & Vendors';

    public static function getNavigationLabel(): string
    {
        return __('Vendor Bills');
    }

    public static function getModelLabel(): string
    {
        return __('Vendor Bills');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('supplier_invoice_number')
                    ->label(__('Supplier Bill #'))
                    ->required()
                    ->maxLength(50),
                Select::make('supplier_id')
                    ->label(__('Supplier'))
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('purchase_order_id')
                    ->label(__('Purchase Order'))
                    ->relationship('purchaseOrder', 'order_number')
                    ->searchable()
                    ->preload(),
                DatePicker::make('invoice_date')
                    ->label(__('Bill Date'))
                    ->default(now())
                    ->required(),
                DatePicker::make('due_date')
                    ->label(__('Due Date'))
                    ->default(now()->addDays(30))
                    ->required(),
                TextInput::make('currency')
                    ->label(__('Currency'))
                    ->default('CHF')
                    ->maxLength(3),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'received' => __('Received'),
                        'approved' => __('Approved'),
                        'paid' => __('Paid'),
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
                TextInput::make('paid_amount')
                    ->label(__('Paid Amount'))
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
                TextColumn::make('supplier_invoice_number')
                    ->label(__('Supplier Bill #'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label(__('Supplier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label(__('Bill Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label(__('Due Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'received',
                        'warning' => 'approved',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('CHF')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label(__('Paid Amount'))
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
            'index' => ManageVendorBills::route('/'),
        ];
    }
}
