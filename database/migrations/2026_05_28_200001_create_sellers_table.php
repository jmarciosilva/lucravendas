<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('tenant_id');
            // Usuário vinculado ao seller (para autenticação no dashboard)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Taxa de comissão em percentual (ex: 10.00 = 10%)
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            // Status: pending | active | suspended
            $table->string('status')->default('pending');
            // Dados bancários para repasse (CNPJ, banco, agência, conta)
            $table->json('bank_info')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Slug único por tenant
            $table->unique(['slug', 'tenant_id']);
            $table->index('tenant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
