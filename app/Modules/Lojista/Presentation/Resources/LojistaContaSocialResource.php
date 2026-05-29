<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Lojista\Presentation\Resources\LojistaContaSocialResource\Pages\ListLojistaContasSociais;
use App\Modules\Marketing\Infrastructure\Models\SocialAccountModel;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LojistaContaSocialResource extends Resource
{
    protected static ?string $model = SocialAccountModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Contas Sociais';

    protected static ?string $modelLabel = 'Conta Social';

    protected static ?string $pluralModelLabel = 'Contas Sociais';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
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

                Tables\Columns\TextColumn::make('account_name')
                    ->label('Conta')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('token_expires_at')
                    ->label('Token expira em')
                    ->dateTime('d/m/Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Conectada em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('desativar')
                    ->label('Desativar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SocialAccountModel $record) => $record->is_active)
                    ->action(function (SocialAccountModel $record): void {
                        $record->update(['is_active' => false]);
                        Notification::make()->title('Conta desativada.')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Somente leitura — contas conectadas via API OAuth
    public static function getPages(): array
    {
        return [
            'index' => ListLojistaContasSociais::route('/'),
        ];
    }
}
