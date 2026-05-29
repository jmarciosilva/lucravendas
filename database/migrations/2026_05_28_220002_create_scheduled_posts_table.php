<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('social_account_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->text('caption');
            $table->string('image_url');
            $table->string('platform', 20); // instagram | facebook
            // pending → publicação aguardada; published → publicado com sucesso
            // failed → erro ao publicar; cancelled → cancelado manualmente
            $table->enum('status', ['pending', 'published', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('publish_at');
            $table->timestamp('published_at')->nullable();
            $table->string('external_post_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'publish_at']);
            $table->index('tenant_id');
            $table->foreign('social_account_id')->references('id')->on('social_accounts')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_posts');
    }
};
