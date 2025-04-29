<?php

namespace App\Providers;

use App\Interfaces\Services\WhatsAppCloudServiceInterface;
use App\Services\WhatsAppCloudService;
use Illuminate\Support\ServiceProvider;

class MetaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppCloudServiceInterface::class, WhatsAppCloudService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
