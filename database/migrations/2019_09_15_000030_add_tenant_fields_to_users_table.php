<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campos de tenant e controle à tabela de usuários.
 *
 * Separado da migration inicial de users para garantir que a tabela tenants
 * já exista quando a FK for adicionada (order por timestamp no nome).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 20)->nullable()->after('email_verified_at');
            $table->string('tenant_id')->nullable()->after('phone');
            $table->string('status')->default('active')->after('tenant_id');
            $table->softDeletes();

            // FK para tenants criada na migration 2019_09_15_000010
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');

            $table->index('tenant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['phone', 'tenant_id', 'status', 'deleted_at']);
        });
    }
};
