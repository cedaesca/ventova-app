<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function (Recipient $recipient) {
            $uuid = \Illuminate\Support\Str::uuid();
            $recipient->uuid = $uuid;
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(RecipientGroup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(RecipientVariable::class);
    }
}
