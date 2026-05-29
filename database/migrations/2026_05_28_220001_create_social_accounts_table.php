<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('platform', 20); // instagram | facebook
            $table->string('account_id'); // ID externo da conta/página
            $table->string('account_name');
            $table->text('access_token'); // token OAuth (armazenado encriptado via cast)
            $table->timestamp('token_expires_at')->nullable();
            $table->string('page_id')->nullable(); // Facebook Page ID
            $table->string('instagram_account_id')->nullable(); // Instagram Business Account ID
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'platform']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
