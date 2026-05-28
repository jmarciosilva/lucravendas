<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela principal de produtos.
 *
 * Preços são armazenados em centavos (inteiro) para evitar
 * problemas de arredondamento de ponto flutuante. A camada
 * de domínio converte para reais via o Value Object Money.
 *
 * Soft delete é usado para preservar histórico de pedidos
 * que referenciam produtos já removidos do catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            // Identificação do produto
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            // Preços armazenados em centavos (ex.: R$ 49,90 = 4990)
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_price')->nullable();

            // Controle de estoque
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);

            // Status: active | inactive | draft
            $table->string('status')->default('draft');

            // Relacionamentos
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Escopo do tenant
            $table->string('tenant_id');

            $table->timestamps();

            // Soft delete preserva histórico de itens de pedido
            $table->softDeletes();

            // Índices para as queries mais comuns
            $table->unique(['slug', 'tenant_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category_id']);
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
