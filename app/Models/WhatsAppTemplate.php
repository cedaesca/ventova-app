<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppTemplate extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function previousCategory(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplateCategory::class, 'previous_category_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplateCategory::class, 'category_id');
    }
}
