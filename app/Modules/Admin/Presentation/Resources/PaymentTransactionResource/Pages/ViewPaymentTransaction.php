<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\PaymentTransactionResource\Pages;

use App\Modules\Admin\Presentation\Resources\PaymentTransactionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Modules\Payments\Application\UseCases\RefundPayment\RefundPaymentHandler;

class ViewPaymentTransaction extends ViewRecord
{
    protected static string $resource = PaymentTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refund')
                ->label('Estornar Pagamento')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Estorno')
                ->modalDescription('Esta ação estornará o pagamento no Mercado Pago. Não pode ser desfeita.')
                ->visible(fn () => $this->record->status === 'approved')
                ->action(function () {
                    try {
                        app(RefundPaymentHandler::class)->handle(
                            $this->record->order_id,
                            $this->record->order->tenant_id ?? '',
                        );

                        Notification::make()
                            ->title('Estorno realizado com sucesso.')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Erro ao estornar: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
