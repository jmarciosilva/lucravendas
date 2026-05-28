<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\PaymentTransactionResource\Pages;

use App\Modules\Admin\Presentation\Resources\PaymentTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;
}
