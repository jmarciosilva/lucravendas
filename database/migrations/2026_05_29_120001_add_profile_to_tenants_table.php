<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona o campo `profile` à tabela de tenants.
 *
 * O profile define o tipo de negócio do tenant e determina quais módulos
 * são habilitados por padrão via feature flags em config/storefront.php.
 * O tema visual (theme) fica no campo data JSON e pode diferir do profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('profile')->default('generico')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn('profile');
        });
    }
};
