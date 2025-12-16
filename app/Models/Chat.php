<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $public_id
 * @property string|null $name
 * @property int|null $user_id
 * @property string|null $user_instruction
 * @property string|null $allowed_domains
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Visitor> $visitors
 * @property-read int|null $visitors_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereAllowedDomains($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUserInstruction($value)
 * @mixin \Eloquent
 */
class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'name',
        'user_id',
        'user_instruction',
        'allowed_domains',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
