<?php

namespace App\Models\Showcase;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperShowcaseUpvote
 */
class ShowcaseUpvote extends Model
{
    protected $fillable = [
        'user_id',
        'showcase_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function showcase(): BelongsTo
    {
        return $this->belongsTo(Showcase::class);
    }
}
