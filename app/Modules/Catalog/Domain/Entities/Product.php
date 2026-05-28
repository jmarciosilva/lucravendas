<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Entities;

use App\Modules\Catalog\Domain\Events\ProductCreated;
use App\Modules\Catalog\Domain\Events\ProductUpdated;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductStatus;
use InvalidArgumentException;

/**
 * Entidade raiz do módulo Catálogo.
 *
 * Concentra todas as regras de negócio relacionadas ao ciclo de vida
 * de um produto: publicação, atualização de preço, controle de estoque.
 * Nunca deve ser instanciada diretamente — use os factory methods.
 */
final class Product
{
    /** @var list<object> Eventos de domínio acumulados para despacho posterior */
    private array $domainEvents = [];

    private function __construct(
        private readonly int $id,
        private string $name,
        private string $slug,
        private ?string $description,
        private Money $price,
        private ?Money $comparePrice,
        private ?string $sku,
        private int $stock,
        private ProductStatus $status,
        private readonly string $tenantId,
        private ?int $categoryId,
    ) {}

    /**
     * Cria um novo produto no status rascunho (draft).
     *
     * O preço de comparação (compare_price) deve ser maior que o preço
     * real para que o desconto faça sentido visualmente no catálogo.
     */
    public static function create(
        int $id,
        string $name,
        string $slug,
        Money $price,
        string $tenantId,
        ?string $description = null,
        ?Money $comparePrice = null,
        ?string $sku = null,
        int $stock = 0,
        ?int $categoryId = null,
    ): self {
        self::validateName($name);
        self::validateSlug($slug);
        self::validateComparePrice($price, $comparePrice);

        $product = new self(
            id: $id,
            name: $name,
            slug: $slug,
            description: $description,
            price: $price,
            comparePrice: $comparePrice,
            sku: $sku,
            stock: $stock,
            status: ProductStatus::draft(),
            tenantId: $tenantId,
            categoryId: $categoryId,
        );

        $product->recordEvent(new ProductCreated($product));

        return $product;
    }

    /**
     * Reconstrói uma entidade a partir dos dados do banco (sem disparar eventos).
     * Usado pelos repositórios para hidratar a entidade após uma consulta.
     */
    public static function restore(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        Money $price,
        ?Money $comparePrice,
        ?string $sku,
        int $stock,
        ProductStatus $status,
        string $tenantId,
        ?int $categoryId,
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            description: $description,
            price: $price,
            comparePrice: $comparePrice,
            sku: $sku,
            stock: $stock,
            status: $status,
            tenantId: $tenantId,
            categoryId: $categoryId,
        );
    }

    /** Publica o produto, tornando-o visível no catálogo */
    public function publish(): void
    {
        $this->status = ProductStatus::active();
        $this->recordEvent(new ProductUpdated($this));
    }

    /** Desativa o produto sem removê-lo do banco */
    public function deactivate(): void
    {
        $this->status = ProductStatus::fromString(ProductStatus::INACTIVE);
    }

    /** Atualiza os campos editáveis do produto */
    public function update(
        string $name,
        ?string $description,
        Money $price,
        ?Money $comparePrice,
        ?string $sku,
        ?int $categoryId,
    ): void {
        self::validateName($name);
        self::validateComparePrice($price, $comparePrice);

        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->comparePrice = $comparePrice;
        $this->sku = $sku;
        $this->categoryId = $categoryId;

        $this->recordEvent(new ProductUpdated($this));
    }

    /**
     * Decrementa o estoque. Lança exceção se não houver quantidade suficiente.
     * O controle de concorrência (lock otimista) é responsabilidade do repositório.
     */
    public function decrementStock(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException('A quantidade a decrementar deve ser maior que zero.');
        }

        if ($this->stock < $quantidade) {
            throw new InvalidArgumentException(
                "Estoque insuficiente. Disponível: {$this->stock}, solicitado: {$quantidade}."
            );
        }

        $this->stock -= $quantidade;
    }

    /** Incrementa o estoque (ex.: devolução ou reposição) */
    public function incrementStock(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException('A quantidade a incrementar deve ser maior que zero.');
        }

        $this->stock += $quantidade;
    }

    /** @return list<object> Retorna e limpa os eventos de domínio pendentes */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    private static function validateName(string $name): void
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('O nome do produto não pode ser vazio.');
        }
    }

    private static function validateSlug(string $slug): void
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException(
                "Slug inválido: '{$slug}'. Use apenas letras minúsculas, números e hífens."
            );
        }
    }

    /** Garante que o preço de comparação seja maior que o preço real */
    private static function validateComparePrice(Money $price, ?Money $comparePrice): void
    {
        if ($comparePrice !== null && ! $price->isLessThan($comparePrice)) {
            throw new InvalidArgumentException(
                'O preço de comparação (de) deve ser maior que o preço atual (por).'
            );
        }
    }

    public function id(): int { return $this->id; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }
    public function price(): Money { return $this->price; }
    public function comparePrice(): ?Money { return $this->comparePrice; }
    public function sku(): ?string { return $this->sku; }
    public function stock(): int { return $this->stock; }
    public function status(): ProductStatus { return $this->status; }
    public function tenantId(): string { return $this->tenantId; }
    public function categoryId(): ?int { return $this->categoryId; }
    public function hasStock(): bool { return $this->stock > 0; }
    public function isActive(): bool { return $this->status->isActive(); }

    /** Verifica se o produto está com estoque baixo (menos de 5 unidades) */
    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock < $threshold;
    }
}
