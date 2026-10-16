<?php

namespace App\Filament\Resources\ContactGroups;

use App\Filament\Resources\ContactGroups\Pages\ManageContactGroups;
use App\Modules\CrmContacts\Models\ContactGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ContactGroupResource extends Resource
{
    protected static ?string $model = ContactGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

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
                Select::make('type')
                    ->options([
                        'general' => 'General',
                        'customer' => 'Customer',
                        'supplier' => 'Supplier',
                        'lead' => 'Lead',
                    ])
                    ->default('general')
                    ->required(),
                TextInput::make('color')
                    ->placeholder('#3B82F6 or blue')
                    ->maxLength(30),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'customer' => 'success',
                        'supplier' => 'info',
                        'lead' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('color'),
                TextColumn::make('memberships_count')
                    ->counts('memberships')
                    ->label('Members Count'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageContactGroups::route('/'),
        ];
    }
}
