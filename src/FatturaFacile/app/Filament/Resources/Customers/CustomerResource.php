<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\ManageCustomers;
use App\Modules\CrmContacts\Handlers\GetCustomerSalesStatsHandler;
use App\Modules\CrmContacts\Queries\GetCustomerSalesStatsQuery;
use App\Modules\Customers\Models\Customer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'CRM & Contacts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->maxLength(50),
                TextInput::make('tax_number')
                    ->label('Swiss UID (CHE-xxx.xxx.xxx)')
                    ->maxLength(50),
                TextInput::make('vat_number')
                    ->maxLength(50),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),
                TextInput::make('street')
                    ->maxLength(255),
                TextInput::make('city')
                    ->maxLength(100),
                TextInput::make('postal_code')
                    ->maxLength(20),
                TextInput::make('country')
                    ->default('CH')
                    ->maxLength(2),
                TextInput::make('currency')
                    ->default('CHF')
                    ->maxLength(3),
                TextInput::make('payment_term_days')
                    ->numeric()
                    ->default(30),
                TextInput::make('credit_limit')
                    ->numeric()
                    ->prefix('CHF')
                    ->default('0.00'),
                Select::make('groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload(),
                Toggle::make('is_active')
                    ->default(true),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('currency')
                    ->sortable(),
                TextColumn::make('payment_term_days')
                    ->label('Terms (Days)'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('salesStats')
                    ->label('Sales Stats')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->color('info')
                    ->modalHeading(fn (Customer $record): string => "Sales Statistics - {$record->name}")
                    ->modalDescription(function (Customer $record): string {
                        /** @var GetCustomerSalesStatsHandler $handler */
                        $handler = app(GetCustomerSalesStatsHandler::class);
                        $stats = $handler->handle(new GetCustomerSalesStatsQuery($record->id));

                        return "Total Invoiced: {$stats->currency} {$stats->totalInvoiced} | Outstanding: {$stats->currency} {$stats->totalOutstanding} | Invoices: {$stats->invoicesCount} | Orders: {$stats->salesOrdersCount} | Quotes: {$stats->quotesCount} | Avg Order Value: {$stats->currency} {$stats->averageOrderValue}";
                    }),
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
            'index' => ManageCustomers::route('/'),
        ];
    }
}
