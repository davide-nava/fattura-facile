<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Modules\CRM\Models\Lead;
use App\Modules\CrmContacts\Commands\ConvertLeadToCustomerCommand;
use App\Modules\CrmContacts\Handlers\ConvertLeadToCustomerHandler;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'CRM & Contacts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('contact_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('company_name')
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),
                Select::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'unqualified' => 'Unqualified',
                        'converted' => 'Converted to Customer',
                    ])
                    ->default('new')
                    ->required(),
                TextInput::make('source')
                    ->maxLength(100),
                Select::make('assigned_to')
                    ->label('Assigned Agent')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'contacted' => 'info',
                        'qualified' => 'warning',
                        'unqualified' => 'danger',
                        'converted' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('source'),
                TextColumn::make('assignedUser.name')
                    ->label('Assigned To'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('convertToCustomer')
                    ->label('Convert')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('success')
                    ->visible(fn (Lead $record): bool => $record->status !== 'converted')
                    ->requiresConfirmation()
                    ->modalHeading('Convert Lead to Customer')
                    ->modalDescription('This will create a new Customer and link all opportunities.')
                    ->action(function (Lead $record): void {
                        /** @var ConvertLeadToCustomerHandler $handler */
                        $handler = app(ConvertLeadToCustomerHandler::class);
                        $customer = $handler->handle(new ConvertLeadToCustomerCommand($record->id));

                        Notification::make()
                            ->title('Lead Converted')
                            ->body("Customer {$customer->name} ({$customer->code}) has been created.")
                            ->success()
                            ->send();
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
            'index' => ManageLeads::route('/'),
        ];
    }
}
