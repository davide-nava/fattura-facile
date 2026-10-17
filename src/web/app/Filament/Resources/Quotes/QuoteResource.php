<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes;

use App\Filament\Resources\Quotes\Pages\ManageQuotes;
use App\Modules\Sales\Models\Quote;
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

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $slug = 'quotes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Sales & Partners';

    public static function getNavigationLabel(): string
    {
        return __('Quotations');
    }

    public static function getModelLabel(): string
    {
        return __('Quotations');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('quote_number')
                    ->label(__('Quote Number'))
                    ->disabled()
                    ->dehydrated(false),
                Select::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('quote_date')
                    ->label(__('Quote Date'))
                    ->default(now())
                    ->required(),
                DatePicker::make('valid_until')
                    ->label(__('Valid Until'))
                    ->default(now()->addDays(30)),
                TextInput::make('currency')
                    ->label(__('Currency'))
                    ->default('CHF')
                    ->maxLength(3),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'sent' => __('Sent'),
                        'accepted' => __('Accepted'),
                        'rejected' => __('Rejected'),
                        'converted' => __('Converted'),
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
                TextColumn::make('quote_number')
                    ->label(__('Quote Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quote_date')
                    ->label(__('Quote Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('Valid Until'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'warning' => 'converted',
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
            'index' => ManageQuotes::route('/'),
        ];
    }
}
