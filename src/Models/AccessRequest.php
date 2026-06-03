<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use MokoGithub\KerberosAuth\Support\Kerberos;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $kerberos
 * @property string $justification
 * @property string $status
 * @property int|null $processed_by
 * @property Carbon|null $processed_at
 * @property string|null $admin_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AccessRequest extends Model
{
    protected $fillable = [
        'user_id',
        'kerberos',
        'justification',
        'status',
        'processed_by',
        'processed_at',
        'admin_message',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Kerberos::userModel());
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Kerberos::userModel(), 'processed_by');
    }

    public function scopePending($query): mixed
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query): mixed
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query): mixed
    {
        return $query->where('status', 'rejected');
    }
}
