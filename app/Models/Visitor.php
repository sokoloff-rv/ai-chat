<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $chat_id
 * @property string $session_id
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property string|null $referrer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Chat $chat
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Visitor whereUserAgent($value)
 * @mixin \Eloquent
 */
class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'session_id',
        'user_agent',
        'ip_address',
        'referrer',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
