<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCalendarEvent extends Model
{
    protected $fillable = [
        'email_calendar_connector_id',
        'event_number',
        'source_type',
        'source_id',
        'event_title',
        'recipient_name',
        'recipient_email',
        'scheduled_start',
        'scheduled_end',
        'location_or_link',
        'send_email',
        'create_calendar_event',
        'email_status',
        'calendar_status',
        'attempts',
        'external_event_id',
        'payload_snapshot',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'send_email' => 'boolean',
        'create_calendar_event' => 'boolean',
        'payload_snapshot' => 'array',
    ];

    public function connector(): BelongsTo
    {
        return $this->belongsTo(EmailCalendarConnector::class, 'email_calendar_connector_id');
    }
}
