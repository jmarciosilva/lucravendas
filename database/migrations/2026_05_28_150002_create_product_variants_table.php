<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela de variantes de produto.
 *
 * Uma variante representa uma combinação de atributos de um produto
 * (ex.: Tamanho M + Cor Azul). Cada variante tem seu próprio estoque,
 * SKU e preço, que podem sobrescrever os do produto pai.
 *
 * O campo `attributes` é um JSON flexível (ex.: {"tamanho": "M", "cor": "Azul"})
 * para evitar a complexidade de tabelas EAV antes de ser necessário.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Nome descritivo da variante (ex.: "M / Azul")
            $table->string('name');

            $table->string('sku')->nullable();

            // Preço em centavos — null significa herdar o preço do produto pai
            $table->unsignedInteger('price')->nullable();

            $table->integer('stock')->default(0);

            // Atributos flexíveis da variante (ex.: {"tamanho": "M", "cor": "Azul"})
            $table->json('attributes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_id');
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
