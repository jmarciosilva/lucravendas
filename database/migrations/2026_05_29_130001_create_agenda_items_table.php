<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: criação da tabela de itens de agenda (eventos, cursos, workshops).
 *
 * Multi-tenancy por coluna tenant_id (FK para tenants.id).
 * Suporta eventos de sellers (seller_id nullable) ou do próprio tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_items', function (Blueprint $table): void {
            // Identificador primário auto-incremento
            $table->id();

            // Relacionamento com o tenant (loja)
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Seller opcional — null indica item do próprio tenant
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            // Tipo do item: evento, curso ou workshop
            $table->enum('type', ['evento', 'curso', 'workshop']);

            // Dados do item
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('featured_image_url')->nullable();

            // Datas de início e fim (obrigatórias — usam useCurrent para compatibilidade com MySQL strict mode)
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->useCurrent();

            // Local do evento (pode ser online, endereço físico, etc.)
            $table->string('location')->nullable();

            // Controle de vagas — null significa ilimitado
            $table->unsignedInteger('slots')->nullable();
            $table->unsignedInteger('slots_used')->default(0);

            // Preço em centavos — 0 significa gratuito
            $table->unsignedInteger('price_centavos')->default(0);

            // Status do item
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->unique(['tenant_id', 'slug']);
            $table->index('tenant_id');
            $table->index('status');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_items');
    }
};
