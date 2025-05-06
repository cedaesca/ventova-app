<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipientVariable extends Model
{
    protected $guarded = [];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }
}
