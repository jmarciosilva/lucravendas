<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Dimensões físicas necessárias para cálculo de frete por transportadora
            $table->unsignedSmallInteger('weight_grams')->nullable()->after('stock');
            $table->unsignedTinyInteger('length_cm')->nullable()->after('weight_grams');
            $table->unsignedTinyInteger('width_cm')->nullable()->after('length_cm');
            $table->unsignedTinyInteger('height_cm')->nullable()->after('width_cm');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight_grams', 'length_cm', 'width_cm', 'height_cm']);
        });
    }
};
