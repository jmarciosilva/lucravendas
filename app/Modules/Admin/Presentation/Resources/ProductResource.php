<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Recurso Filament para gerenciamento de produtos do catálogo.
 *
 * Exibe produtos de todos os tenants com filtros por status e tenant.
 * O formulário inclui upload de imagens via Spatie MediaLibrary.
 */
class ProductResource extends Resource
{
    protected static ?string $model = ProductModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informações Básicas')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do Produto')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation): void {
                        // Gera slug apenas na criação — não altera em edições
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(220),

                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->maxLength(5000)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(100),

                Forms\Components\TextInput::make('tenant_id')
                    ->label('Tenant ID')
                    ->required()
                    ->maxLength(36),

                Forms\Components\Select::make('category_id')
                    ->label('Categoria')
                    ->options(CategoryModel::query()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Preço e Estoque')->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Preço (R$)')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    // Exibe e recebe em reais; o banco armazena em centavos
                    ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100))
                    ->formatStateUsing(fn ($state) => $state / 100),

                Forms\Components\TextInput::make('compare_price')
                    ->label('Preço "De" (R$)')
                    ->numeric()
                    ->nullable()
                    ->dehydrateStateUsing(fn ($state) => $state ? (int) round((float) $state * 100) : null)
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : null),

                Forms\Components\TextInput::make('stock')
                    ->label('Estoque')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft'    => 'Rascunho',
                        'active'   => 'Publicado',
                        'inactive' => 'Inativo',
                    ])
                    ->default('draft')
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Imagens')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label('Imagens do Produto')
                    ->collection('images')
                    ->multiple()
                    ->reorderable()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('thumb')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->width(50)
                    ->height(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    // Converte centavos para reais na exibição
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Estoque')
                    ->sortable()
                    // Destaca em vermelho produtos com estoque baixo (< 5)
                    ->color(fn ($state) => $state < 5 ? 'danger' : null),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'danger'  => 'inactive',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'    => 'Rascunho',
                        'active'   => 'Publicado',
                        'inactive' => 'Inativo',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'    => 'Rascunho',
                        'active'   => 'Publicado',
                        'inactive' => 'Inativo',
                    ]),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Estoque baixo (< 5)')
                    ->query(fn ($query) => $query->where('stock', '<', 5)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return array<string, \Filament\Resources\Pages\PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index'  => \App\Modules\Admin\Presentation\Resources\ProductResource\Pages\ListProducts::route('/'),
            'create' => \App\Modules\Admin\Presentation\Resources\ProductResource\Pages\CreateProduct::route('/create'),
            'edit'   => \App\Modules\Admin\Presentation\Resources\ProductResource\Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
