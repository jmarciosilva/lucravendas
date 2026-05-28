<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Recurso Filament para gerenciamento de categorias do catálogo.
 *
 * Disponível apenas no painel admin (super_admin).
 * Exibe todas as categorias de todos os tenants com filtro por tenant.
 */
class CategoryResource extends Resource
{
    protected static ?string $model = CategoryModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categorias';

    protected static ?string $modelLabel = 'Categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados da Categoria')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(100)
                    // Gera o slug automaticamente enquanto o usuário digita o nome
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                        $set('slug', Str::slug($state ?? ''));
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(120),

                Forms\Components\TextInput::make('tenant_id')
                    ->label('Tenant ID')
                    ->required()
                    ->maxLength(36),

                Forms\Components\Select::make('parent_id')
                    ->label('Categoria Pai')
                    ->options(CategoryModel::query()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->placeholder('Nenhuma (categoria raiz)'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordem de exibição')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('Ativa')
                    ->default(true),
            ])->columns(2),
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

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoria Pai')
                    ->default('—'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Apenas ativas')
                    ->falseLabel('Apenas inativas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    /** @return array<string, \Filament\Resources\Pages\PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index'  => \App\Modules\Admin\Presentation\Resources\CategoryResource\Pages\ListCategories::route('/'),
            'create' => \App\Modules\Admin\Presentation\Resources\CategoryResource\Pages\CreateCategory::route('/create'),
            'edit'   => \App\Modules\Admin\Presentation\Resources\CategoryResource\Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
