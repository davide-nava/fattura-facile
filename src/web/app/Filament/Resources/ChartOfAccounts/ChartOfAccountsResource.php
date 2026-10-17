<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChartOfAccounts;

use App\Filament\Resources\ChartOfAccounts\Pages\ManageChartOfAccounts;
use App\Modules\Accounting\Models\Account;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ChartOfAccountsResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $slug = 'chart-of-accounts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Accounting';

    public static function getNavigationLabel(): string
    {
        return __('Chart of Accounts');
    }

    public static function getModelLabel(): string
    {
        return __('Chart of Accounts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('Account Code'))
                    ->required()
                    ->maxLength(20),
                TextInput::make('name')
                    ->label(__('Account Name'))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('Account Type'))
                    ->options([
                        'asset' => __('Asset (Attivo)'),
                        'liability' => __('Liability (Passivo)'),
                        'equity' => __('Equity (Capitale Proprio)'),
                        'revenue' => __('Revenue (Ricavi)'),
                        'expense' => __('Expense (Costi)'),
                    ])
                    ->required(),
                TextInput::make('currency')
                    ->label(__('Currency'))
                    ->default('CHF')
                    ->maxLength(3),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Account Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Account Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Account Type'))
                    ->badge()
                    ->colors([
                        'info' => 'asset',
                        'danger' => 'liability',
                        'primary' => 'equity',
                        'success' => 'revenue',
                        'warning' => 'expense',
                    ]),
                TextColumn::make('currency')
                    ->label(__('Currency'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
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
            'index' => ManageChartOfAccounts::route('/'),
        ];
    }
}
