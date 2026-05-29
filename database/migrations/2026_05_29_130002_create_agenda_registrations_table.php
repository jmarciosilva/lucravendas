<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: criação da tabela de inscrições em itens de agenda.
 *
 * Cada inscrição vincula um usuário a um item de agenda.
 * Unicidade por (agenda_item_id, user_id) impede inscrições duplicadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_registrations', function (Blueprint $table): void {
            // Identificador primário auto-incremento
            $table->id();

            // Relacionamento com o item de agenda
            $table->unsignedBigInteger('agenda_item_id');
            $table->foreign('agenda_item_id')->references('id')->on('agenda_items')->onDelete('cascade');

            // Relacionamento com o usuário inscrito
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Status da inscrição
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');

            // Timestamps de transição de status
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Observações opcionais do inscrito
            $table->string('notes')->nullable();

            $table->timestamps();

            // Índices para performance e unicidade
            $table->unique(['agenda_item_id', 'user_id']);
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_registrations');
    }
};
