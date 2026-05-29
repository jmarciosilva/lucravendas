<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource\Pages\CreateLojistaCategoria;
use App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource\Pages\EditLojistaCategoria;
use App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource\Pages\ListLojistaCategoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LojistaCategoriaResource extends Resource
{
    protected static ?string $model = CategoryModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categorias';

    protected static ?string $modelLabel = 'Categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        $tenantId = auth()->user()?->tenant_id ?? '';

        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(150)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation): void {
                    if ($operation === 'create') {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(170)
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(),

            Forms\Components\Select::make('parent_id')
                ->label('Categoria Pai')
                ->options(
                    CategoryModel::where('tenant_id', $tenantId)
                        ->whereNull('parent_id')
                        ->pluck('name', 'id')
                )
                ->nullable()
                ->searchable(),

            Forms\Components\TextInput::make('sort_order')
                ->label('Ordem')
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativa')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoria Pai')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLojistaCategoria::route('/'),
            'create' => CreateLojistaCategoria::route('/create'),
            'edit'   => EditLojistaCategoria::route('/{record}/edit'),
        ];
    }
}
