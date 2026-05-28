<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $table->string('tenant_id');
            $table->string('name');                          // ex: "PAC - Sudeste"
            $table->string('carrier')->default('custom');    // correios|melhorenvio|custom
            $table->string('service_code')->nullable();      // código do serviço na transportadora
            // Valores em centavos
            $table->unsignedInteger('base_price');           // preço base da entrega
            $table->unsignedInteger('price_per_kg')->default(0); // adicional por kg
            $table->unsignedTinyInteger('min_days')->default(1);
            $table->unsignedTinyInteger('max_days')->default(10);
            // Subtotal mínimo para frete grátis (null = sem frete grátis)
            $table->unsignedInteger('free_shipping_threshold')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->index('zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
