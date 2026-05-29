<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource\Pages\CreateLojistaProduto;
use App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource\Pages\EditLojistaProduto;
use App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource\Pages\ListLojistaProdutos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LojistaProdutoResource extends Resource
{
    protected static ?string $model = ProductModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 1;

    /** Escopa automaticamente todos os registros ao tenant do lojista autenticado */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        $tenantId = auth()->user()?->tenant_id ?? '';

        return $form->schema([
            Forms\Components\Section::make('Informações Básicas')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do Produto')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(220)
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(),

                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->maxLength(5000)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(100),

                // Apenas categorias do próprio tenant aparecem no select
                Forms\Components\Select::make('category_id')
                    ->label('Categoria')
                    ->options(
                        CategoryModel::where('tenant_id', $tenantId)
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Preço e Estoque')->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Preço (R$)')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
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

            Forms\Components\Section::make('Dimensões para Frete')->schema([
                Forms\Components\TextInput::make('weight_grams')
                    ->label('Peso (gramas)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('length_cm')
                    ->label('Comprimento (cm)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('width_cm')
                    ->label('Largura (cm)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('height_cm')
                    ->label('Altura (cm)')
                    ->numeric()
                    ->nullable(),
            ])->columns(4)->collapsed(),

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

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Estoque')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state < 5 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'draft'    => 'warning',
                        'inactive' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'   => 'Publicado',
                        'draft'    => 'Rascunho',
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

    public static function getPages(): array
    {
        return [
            'index'  => ListLojistaProdutos::route('/'),
            'create' => CreateLojistaProduto::route('/create'),
            'edit'   => EditLojistaProduto::route('/{record}/edit'),
        ];
    }
}
