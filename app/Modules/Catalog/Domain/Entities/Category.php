<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Entities;

use InvalidArgumentException;

/**
 * Entidade de domínio que representa uma categoria do catálogo.
 *
 * Categorias podem ser hierárquicas (parent_id). A regra de negócio
 * de que o slug deve ser único por tenant é validada no repositório,
 * pois requer consulta ao banco de dados.
 */
final class Category
{
    private function __construct(
        private readonly int $id,
        private string $name,
        private string $slug,
        private readonly string $tenantId,
        private readonly ?int $parentId,
        private bool $isActive,
        private int $sortOrder,
    ) {}

    /**
     * Cria uma nova categoria.
     *
     * @param string      $name     Nome de exibição da categoria
     * @param string      $slug     Identificador URL-friendly
     * @param string      $tenantId UUID do tenant dono da categoria
     * @param int|null    $parentId ID da categoria pai (null = raiz)
     */
    public static function create(
        int $id,
        string $name,
        string $slug,
        string $tenantId,
        ?int $parentId = null,
        int $sortOrder = 0,
    ): self {
        if (trim($name) === '') {
            throw new InvalidArgumentException('O nome da categoria não pode ser vazio.');
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException(
                "Slug inválido: '{$slug}'. Use apenas letras minúsculas, números e hífens."
            );
        }

        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            tenantId: $tenantId,
            parentId: $parentId,
            isActive: true,
            sortOrder: $sortOrder,
        );
    }

    public function rename(string $newName): void
    {
        if (trim($newName) === '') {
            throw new InvalidArgumentException('O nome da categoria não pode ser vazio.');
        }

        $this->name = $newName;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function id(): int { return $this->id; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function tenantId(): string { return $this->tenantId; }
    public function parentId(): ?int { return $this->parentId; }
    public function isActive(): bool { return $this->isActive; }
    public function sortOrder(): int { return $this->sortOrder; }
    public function isRoot(): bool { return $this->parentId === null; }
}
