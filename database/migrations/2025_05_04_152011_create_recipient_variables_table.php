<?php

use App\Models\Recipient;
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
        Schema::create('recipient_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Recipient::class)->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->boolean('is_datetime')->default(false);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipient_variables');
    }
};
