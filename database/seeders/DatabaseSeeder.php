<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WhatsAppTemplate;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TestAccountSeeder::class,
            WhatsAppTemplateCategorySeeder::class,
        ]);
    }
}
