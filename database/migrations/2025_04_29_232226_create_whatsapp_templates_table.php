<?php

use App\Enums\ResourceStatusesEnum;
use App\Models\User;
use App\Models\WhatsAppTemplateCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(WhatsAppTemplateCategory::class, 'previous_category_id')->constrained();
            $table->foreignIdFor(WhatsAppTemplateCategory::class, 'category_id')->constrained();
            $table->string('meta_template_id')->nullable();
            $table->string('meta_reject_reason')->nullable();
            $table->string('name');
            $table->enum('status', ResourceStatusesEnum::values())->default(ResourceStatusesEnum::PENDING->value);
            $table->string('language_code', 8);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
