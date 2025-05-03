<?php

namespace App\Models;

use App\Enums\ResourceStatusesEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppTemplate extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $table = 'whatsapp_templates';

    protected $casts = [
        'status' => ResourceStatusesEnum::class
    ];

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
