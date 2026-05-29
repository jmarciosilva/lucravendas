<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent da tabela `agenda_registrations`.
 *
 * Representa a inscrição de um usuário em um item de agenda.
 * Status: pending (pago necessário), confirmed (gratuito ou pago), cancelled.
 */
class AgendaRegistrationModel extends Model
{
    protected $table = 'agenda_registrations';

    /** @var list<string> */
    protected $fillable = [
        'agenda_item_id',
        'user_id',
        'status',
        'confirmed_at',
        'cancelled_at',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /** Item de agenda ao qual esta inscrição pertence */
    public function agendaItem(): BelongsTo
    {
        return $this->belongsTo(AgendaItemModel::class, 'agenda_item_id');
    }

    /** Usuário inscrito */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
