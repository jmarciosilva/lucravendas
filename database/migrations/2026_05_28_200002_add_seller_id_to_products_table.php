<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Seller responsável por este produto — nullable pois produtos podem ser do próprio tenant
            $table->foreignId('seller_id')
                ->nullable()
                ->after('category_id')
                ->constrained('sellers')
                ->nullOnDelete();

            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn('seller_id');
        });
    }
};
