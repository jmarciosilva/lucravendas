<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Agenda\Application\UseCases\CancelAgendaItem\CancelAgendaItemHandler;
use App\Modules\Agenda\Application\UseCases\PublishAgendaItem\PublishAgendaItemHandler;
use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource\Pages\CreateLojistaAgenda;
use App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource\Pages\EditLojistaAgenda;
use App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource\Pages\ListLojistaAgenda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resource Filament para gerenciamento de agenda no painel do lojista.
 *
 * Permite criar, editar, publicar e cancelar eventos, cursos e workshops.
 * Todos os registros são escopados automaticamente ao tenant do lojista.
 */
class LojistaAgendaResource extends Resource
{
    protected static ?string $model = AgendaItemModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $modelLabel = 'Item de Agenda';

    protected static ?string $pluralModelLabel = 'Agenda';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 1;

    /** Escopa todos os registros ao tenant do lojista autenticado */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do Evento')->schema([

                // Tipo do item: evento, curso ou workshop
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'evento'   => 'Evento',
                        'curso'    => 'Curso',
                        'workshop' => 'Workshop',
                    ])
                    ->required(),

                // Título principal do item
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                // Descrição curta para listagens e redes sociais
                Forms\Components\Textarea::make('short_description')
                    ->label('Descrição Curta')
                    ->maxLength(500)
                    ->rows(2)
                    ->columnSpanFull(),

                // Descrição completa com suporte a formatação
                Forms\Components\Textarea::make('description')
                    ->label('Descrição Completa')
                    ->rows(5)
                    ->columnSpanFull(),

                // URL da imagem de destaque (hospedada externamente)
                Forms\Components\TextInput::make('featured_image_url')
                    ->label('URL da Imagem de Destaque')
                    ->url()
                    ->nullable()
                    ->placeholder('https://exemplo.com/imagem.jpg')
                    ->columnSpanFull(),

                // Local do evento — pode ser endereço físico ou link online
                Forms\Components\TextInput::make('location')
                    ->label('Local')
                    ->nullable()
                    ->placeholder('Endereço físico ou link de transmissão')
                    ->columnSpanFull(),

                // Data e hora de início
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Data de Início')
                    ->required(),

                // Data e hora de término
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Data de Término')
                    ->required(),

                // Número máximo de vagas — vazio significa ilimitado
                Forms\Components\TextInput::make('slots')
                    ->label('Vagas')
                    ->numeric()
                    ->nullable()
                    ->minValue(1)
                    ->placeholder('Vazio = ilimitado'),

                // Preço em centavos — 0 significa gratuito
                Forms\Components\TextInput::make('price_centavos')
                    ->label('Preço (centavos)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('0 = gratuito, valor em centavos (ex: R$50,00 = 5000)'),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Título com link para edição
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                // Tipo com badge colorido
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'evento'   => 'info',
                        'curso'    => 'primary',
                        'workshop' => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'evento'   => 'Evento',
                        'curso'    => 'Curso',
                        'workshop' => 'Workshop',
                        default    => $state,
                    }),

                // Data de início formatada
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Início')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                // Status com badge colorido
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft'     => 'gray',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Publicado',
                        'draft'     => 'Rascunho',
                        'cancelled' => 'Cancelado',
                        default     => $state,
                    }),

                // Coluna "Inscritos" mostrando slots_used / slots
                Tables\Columns\TextColumn::make('slots_used')
                    ->label('Inscritos')
                    ->formatStateUsing(function (AgendaItemModel $record): string {
                        return $record->slots !== null
                            ? "{$record->slots_used} / {$record->slots}"
                            : "{$record->slots_used} / ∞";
                    }),
            ])
            ->filters([
                // Filtro por status
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Rascunho',
                        'published' => 'Publicado',
                        'cancelled' => 'Cancelado',
                    ]),

                // Filtro por tipo
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'evento'   => 'Evento',
                        'curso'    => 'Curso',
                        'workshop' => 'Workshop',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Ação de publicar: disponível apenas para itens em rascunho
                Tables\Actions\Action::make('publicar')
                    ->label('Publicar')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publicar item de agenda')
                    ->modalDescription('O item ficará visível na vitrine da loja.')
                    ->visible(fn (AgendaItemModel $record): bool => $record->status === 'draft')
                    ->action(function (AgendaItemModel $record): void {
                        app(PublishAgendaItemHandler::class)->handle(
                            $record->id,
                            auth()->user()?->tenant_id ?? ''
                        );
                        Notification::make()->title('Item publicado com sucesso!')->success()->send();
                    }),

                // Ação de cancelar: disponível para itens publicados ou em rascunho
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar item de agenda')
                    ->modalDescription('Esta ação cancela o item. Os inscritos não serão notificados automaticamente.')
                    ->visible(fn (AgendaItemModel $record): bool => in_array($record->status, ['draft', 'published']))
                    ->action(function (AgendaItemModel $record): void {
                        app(CancelAgendaItemHandler::class)->handle(
                            $record->id,
                            auth()->user()?->tenant_id ?? ''
                        );
                        Notification::make()->title('Item cancelado.')->warning()->send();
                    }),
            ])
            ->defaultSort('starts_at', 'asc');
    }

    /** Injeta o tenant_id do lojista ao criar um novo item */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id']  = auth()->user()?->tenant_id ?? '';
        $data['slots_used'] = 0;
        $data['status']     = 'draft';

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLojistaAgenda::route('/'),
            'create' => CreateLojistaAgenda::route('/create'),
            'edit'   => EditLojistaAgenda::route('/{record}/edit'),
        ];
    }
}
