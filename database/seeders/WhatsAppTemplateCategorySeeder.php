<?php

namespace Database\Seeders;

use App\Enums\WhatsAppTemplateCategoriesEnum;
use App\Models\WhatsAppTemplateCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WhatsAppTemplateCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WhatsAppTemplateCategory::create([
            'meta_code' => WhatsAppTemplateCategoriesEnum::UTILITY->value,
            'name' => 'Utilidad',
        ]);

        WhatsAppTemplateCategory::create([
            'meta_code' => WhatsAppTemplateCategoriesEnum::MARKETING->value,
            'name' => 'Marketing',
        ]);

        WhatsAppTemplateCategory::create([
            'meta_code' => WhatsAppTemplateCategoriesEnum::AUTHENTICATION->value,
            'name' => 'Autenticación',
        ]);
    }
}
