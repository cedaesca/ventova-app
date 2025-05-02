<?php

use App\Enums\WhatsAppTemplateCategoriesEnum;
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
        Schema::create('whatsapp_template_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('meta_code', WhatsAppTemplateCategoriesEnum::values())->unique();
            $table->string('name');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_template_categories');
    }
};
