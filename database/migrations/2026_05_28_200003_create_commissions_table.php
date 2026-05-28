<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            // Item do pedido que originou a comissão
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->restrictOnDelete();
            // Valores monetários em centavos
            $table->unsignedInteger('gross_amount');         // total bruto do item (preço × qtd)
            $table->unsignedInteger('commission_amount');    // valor da comissão da plataforma
            $table->unsignedInteger('net_amount');           // valor líquido para o seller
            // Status: pending | paid | cancelled
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
