<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WhatsAppTemplateCategory extends Model
{
    protected $guarded = [];

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null) {}

    public function WhatsAppTemplates(): BelongsToMany
    {
        return $this->belongsToMany(
            WhatsAppTemplate::class,
            'whatsapp_template_whatsapp_template_category',
            'whatsapp_template_category_id',
            'whatsapp_template_id'
        )->withTimestamps();
    }
}
