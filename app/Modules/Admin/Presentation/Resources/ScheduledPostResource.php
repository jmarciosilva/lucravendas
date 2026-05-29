<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\ScheduledPostResource\Pages\ListScheduledPosts;
use App\Modules\Admin\Presentation\Resources\ScheduledPostResource\Pages\ViewScheduledPost;
use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScheduledPostResource extends Resource
{
    protected static ?string $model = ScheduledPostModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Posts Agendados';

    protected static ?string $modelLabel = 'Post Agendado';

    protected static ?string $pluralModelLabel = 'Posts Agendados';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('platform')->label('Plataforma')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
            Textarea::make('caption')->label('Legenda')->disabled()->rows(4),
            TextInput::make('image_url')->label('URL da Imagem')->disabled(),
            TextInput::make('publish_at')->label('Agendar para')->disabled(),
            TextInput::make('published_at')->label('Publicado em')->disabled(),
            TextInput::make('external_post_id')->label('ID do Post')->disabled(),
            Textarea::make('error_message')->label('Mensagem de Erro')->disabled(),
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

                TextColumn::make('caption')
                    ->label('Legenda')
                    ->limit(60)
                    ->tooltip(fn (ScheduledPostModel $record) => $record->caption),

                TextColumn::make('status')
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

                TextColumn::make('publish_at')
                    ->label('Agendado para')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publicado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        PostStatus::PENDING   => 'Pendente',
                        PostStatus::PUBLISHED => 'Publicado',
                        PostStatus::FAILED    => 'Falhou',
                        PostStatus::CANCELLED => 'Cancelado',
                    ]),

                SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook'  => 'Facebook',
                    ]),
            ])
            ->actions([
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ScheduledPostModel $record) => $record->status === PostStatus::PENDING)
                    ->action(function (ScheduledPostModel $record): void {
                        $record->update(['status' => PostStatus::CANCELLED]);

                        Notification::make()
                            ->title('Post cancelado com sucesso.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('publish_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduledPosts::route('/'),
            'view'  => ViewScheduledPost::route('/{record}'),
        ];
    }
}
