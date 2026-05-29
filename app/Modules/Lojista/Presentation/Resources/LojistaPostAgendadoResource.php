<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Lojista\Presentation\Resources\LojistaPostAgendadoResource\Pages\ListLojistaPostsAgendados;
use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LojistaPostAgendadoResource extends Resource
{
    protected static ?string $model = ScheduledPostModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Posts Agendados';

    protected static ?string $modelLabel = 'Post Agendado';

    protected static ?string $pluralModelLabel = 'Posts Agendados';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

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

                Tables\Columns\TextColumn::make('caption')
                    ->label('Legenda')
                    ->limit(60)
                    ->tooltip(fn (ScheduledPostModel $record) => $record->caption),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PostStatus::PENDING   => 'warning',
                        PostStatus::PUBLISHED => 'success',
                        PostStatus::FAILED    => 'danger',
                        PostStatus::CANCELLED => 'gray',
                        default               => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PostStatus::PENDING   => 'Pendente',
                        PostStatus::PUBLISHED => 'Publicado',
                        PostStatus::FAILED    => 'Falhou',
                        PostStatus::CANCELLED => 'Cancelado',
                        default               => $state,
                    }),

                Tables\Columns\TextColumn::make('publish_at')
                    ->label('Agendado para')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        PostStatus::PENDING   => 'Pendente',
                        PostStatus::PUBLISHED => 'Publicado',
                        PostStatus::FAILED    => 'Falhou',
                        PostStatus::CANCELLED => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options(['instagram' => 'Instagram', 'facebook' => 'Facebook']),
            ])
            ->actions([
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ScheduledPostModel $record) => $record->status === PostStatus::PENDING)
                    ->action(function (ScheduledPostModel $record): void {
                        $record->update(['status' => PostStatus::CANCELLED]);
                        Notification::make()->title('Post cancelado.')->success()->send();
                    }),
            ])
            ->defaultSort('publish_at', 'desc');
    }

    // Somente leitura — posts criados via API
    public static function getPages(): array
    {
        return [
            'index' => ListLojistaPostsAgendados::route('/'),
        ];
    }
}
