<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrHelpdeskMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_helpdesk_ticket_id',
        'sender_user_id',
        'sender_type',
        'message_body',
        'attachment_reference',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(HrHelpdeskTicket::class, 'hr_helpdesk_ticket_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
