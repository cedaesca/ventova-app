<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppTemplateCategory extends Model
{
    protected $guarded = [];
    protected $table = 'whatsapp_template_categories';

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null) {}

    public function WhatsAppTemplates(): HasMany
    {
        return $this->hasMany(WhatsAppTemplate::class, 'category_id');
    }
}
