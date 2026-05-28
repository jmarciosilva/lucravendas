<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->restrictOnDelete();
            // Valor total do repasse em centavos
            $table->unsignedInteger('amount');
            // Status: pending | processing | paid | failed
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            // Resposta do gateway no momento do repasse
            $table->json('gateway_response')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
