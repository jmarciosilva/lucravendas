<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\SocialAccountResource\Pages\ListSocialAccounts;
use App\Modules\Marketing\Infrastructure\Models\SocialAccountModel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class SocialAccountResource extends Resource
{
    protected static ?string $model = SocialAccountModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Contas Sociais';

    protected static ?string $modelLabel = 'Conta Social';

    protected static ?string $pluralModelLabel = 'Contas Sociais';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('tenant_id')->label('Tenant')->disabled(),
            TextInput::make('platform')->label('Plataforma')->disabled(),
            TextInput::make('account_name')->label('Nome da Conta')->disabled(),
            TextInput::make('account_id')->label('ID Externo')->disabled(),
            TextInput::make('token_expires_at')->label('Expira em')->disabled(),
            Toggle::make('is_active')->label('Ativa')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('platform')
                    ->label('Plataforma')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'instagram' => 'info',
                        'facebook'  => 'primary',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'instagram' => 'Instagram',
                        'facebook'  => 'Facebook',
                        default     => $state,
                    }),

                TextColumn::make('account_name')
                    ->label('Conta')
                    ->searchable(),

                TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),

                TextColumn::make('token_expires_at')
                    ->label('Token expira em')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Conectada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook'  => 'Facebook',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Ativa'),
            ])
            ->actions([
                Action::make('deactivate')
                    ->label('Desativar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SocialAccountModel $record) => $record->is_active)
                    ->action(function (SocialAccountModel $record): void {
                        $record->update(['is_active' => false]);

                        Notification::make()
                            ->title('Conta desativada com sucesso.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialAccounts::route('/'),
        ];
    }
}
