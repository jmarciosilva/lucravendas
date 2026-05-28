<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('code', 50);
            $table->enum('type', ['percent', 'fixed']);
            // Valor do desconto: % para percent (ex: 10 = 10%) ou centavos para fixed
            $table->unsignedInteger('value');
            // Valor mínimo do pedido em centavos para o cupom ser válido
            $table->unsignedInteger('min_order_value')->default(0);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['code', 'tenant_id']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
