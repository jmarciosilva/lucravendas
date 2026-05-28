<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela de categorias do catálogo.
 *
 * Categorias são escopadas por tenant e suportam hierarquia
 * via auto-relacionamento (parent_id). Um produto pode pertencer
 * a uma categoria folha (sem filhos) ou a qualquer nível da árvore.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();

            // Identificação da categoria
            $table->string('name');
            $table->string('slug');

            // Auto-relacionamento para hierarquia de categorias (nullable = categoria raiz)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Escopo do tenant — cada loja tem seu próprio conjunto de categorias
            $table->string('tenant_id');

            // Ordem de exibição dentro do mesmo nível hierárquico
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Slug único por tenant, evitando colisão entre lojas diferentes
            $table->unique(['slug', 'tenant_id']);
            $table->index('tenant_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
