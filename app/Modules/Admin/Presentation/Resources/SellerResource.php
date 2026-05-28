<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\SellerResource\Pages\ListSellers;
use App\Modules\Admin\Presentation\Resources\SellerResource\Pages\ViewSeller;
use App\Modules\Marketplace\Application\UseCases\ApproveSeller\ApproveSellerCommand;
use App\Modules\Marketplace\Application\UseCases\ApproveSeller\ApproveSellerHandler;
use App\Modules\Marketplace\Application\UseCases\SuspendSeller\SuspendSellerCommand;
use App\Modules\Marketplace\Application\UseCases\SuspendSeller\SuspendSellerHandler;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SellerResource extends Resource
{
    protected static ?string $model = SellerModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Sellers';

    protected static ?string $modelLabel = 'Seller';

    protected static ?string $pluralModelLabel = 'Sellers';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Nome')->disabled(),
            TextInput::make('slug')->label('Slug')->disabled(),
            TextInput::make('tenant_id')->label('Tenant')->disabled(),
            TextInput::make('commission_rate')
                ->label('Taxa de Comissão (%)')
                ->numeric()
                ->step(0.01),
            Textarea::make('description')->label('Descrição')->disabled(),
            KeyValue::make('bank_info')->label('Dados Bancários'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')->label('Slug')->searchable(),

                TextColumn::make('tenant_id')->label('Tenant'),

                TextColumn::make('commission_rate')
                    ->label('Comissão')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'pending'   => 'warning',
                        'suspended' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'    => 'Ativo',
                        'pending'   => 'Pendente',
                        'suspended' => 'Suspenso',
                        default     => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pendente',
                        'active'    => 'Ativo',
                        'suspended' => 'Suspenso',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SellerModel $record) => $record->status === 'pending')
                    ->action(function (SellerModel $record) {
                        app(ApproveSellerHandler::class)->handle(new ApproveSellerCommand($record->id));
                        Notification::make()->title('Seller aprovado com sucesso.')->success()->send();
                    }),

                Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SellerModel $record) => $record->status === 'active')
                    ->action(function (SellerModel $record) {
                        app(SuspendSellerHandler::class)->handle(new SuspendSellerCommand($record->id));
                        Notification::make()->title('Seller suspenso.')->warning()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSellers::route('/'),
            'view'  => ViewSeller::route('/{record}'),
        ];
    }
}
