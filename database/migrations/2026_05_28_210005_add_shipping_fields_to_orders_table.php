<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Endereço de entrega do destinatário
            $table->string('recipient_name')->nullable()->after('notes');
            $table->string('recipient_zipcode', 9)->nullable()->after('recipient_name');
            $table->string('recipient_address')->nullable()->after('recipient_zipcode');
            $table->string('recipient_number', 20)->nullable()->after('recipient_address');
            $table->string('recipient_complement')->nullable()->after('recipient_number');
            $table->string('recipient_city')->nullable()->after('recipient_complement');
            $table->string('recipient_state', 2)->nullable()->after('recipient_city');

            // Código do serviço de frete escolhido (ex: "2" = SEDEX no ME)
            $table->string('shipping_service_code')->nullable()->after('recipient_state');

            // Dados de rastreio e etiqueta (preenchidos pós-pagamento)
            $table->string('tracking_code')->nullable()->after('shipping_service_code');
            $table->string('tracking_status')->nullable()->after('tracking_code');
            $table->string('shipping_label_url')->nullable()->after('tracking_status');

            $table->index('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['tracking_code']);
            $table->dropColumn([
                'recipient_name', 'recipient_zipcode', 'recipient_address',
                'recipient_number', 'recipient_complement', 'recipient_city', 'recipient_state',
                'shipping_service_code', 'tracking_code', 'tracking_status', 'shipping_label_url',
            ]);
        });
    }
};
