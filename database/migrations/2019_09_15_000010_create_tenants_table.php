<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela central de tenants (lojas).
 *
 * Armazena metadados públicos de cada tenant. Dados sensíveis e
 * configurações específicas por tenant vivem no campo `data` (JSON),
 * acessíveis via castings do modelo Eloquent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            // UUID gerado pelo stancl/tenancy; chave primária string por design do pacote
            $table->string('id')->primary();

            $table->string('name');
            $table->string('slug')->unique();

            // Plano contratado — validado por enum na camada de domínio
            $table->string('plan')->default('free');

            // Status do tenant: active, suspended, trial, cancelled
            $table->string('status')->default('active');

            // Metadados dinâmicos: configurações, integrações, limites por plano
            $table->json('data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
