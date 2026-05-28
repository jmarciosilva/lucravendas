<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('gateway')->default('mercadopago');
            $table->string('external_id')->nullable();
            $table->string('method'); // pix | credit_card | boleto
            $table->unsignedInteger('amount'); // centavos
            $table->string('status')->default('pending');
            $table->text('qr_code')->nullable();
            $table->text('qr_code_base64')->nullable();
            $table->string('ticket_url')->nullable();
            $table->unsignedTinyInteger('installments')->default(1);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->unique('external_id');
            $table->index(['gateway', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
