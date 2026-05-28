<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Models\User;
use App\Modules\Admin\Presentation\Resources\UserResource\Pages\CreateUser;
use App\Modules\Admin\Presentation\Resources\UserResource\Pages\EditUser;
use App\Modules\Admin\Presentation\Resources\UserResource\Pages\ListUsers;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Resource Filament para gerenciamento de usuários pelo admin LucraOne.
 *
 * Exibe todos os usuários da plataforma (super_admins, tenant_admins e customers).
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Usuários';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('password')
                ->label('Senha')
                ->password()
                ->minLength(8)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create'),

            TextInput::make('phone')
                ->label('Telefone')
                ->maxLength(20)
                ->nullable(),

            Select::make('roles')
                ->label('Papel (Role)')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'active'    => 'Ativo',
                    'suspended' => 'Suspenso',
                ])
                ->required()
                ->default('active'),
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

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Papéis')
                    ->badge(),

                TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->placeholder('LucraOne')
                    ->limit(8),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'suspended' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active'    => 'Ativo',
                        'suspended' => 'Suspenso',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return array<string, class-string> */
    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
