<?php

namespace App\Services;

use App\Interfaces\Services\WhatsAppCloudServiceInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService implements WhatsAppCloudServiceInterface
{
    private string $version;
    private string $senderId;
    private string $apiBaseUrl;
    private string $accessToken;
    private string $businessId;

    public function __construct()
    {
        $metaConfig = config('services.meta');

        $this->setApiBaseUrl($metaConfig['api_base_url'])
            ->setVersion($metaConfig['api_version'])
            ->setAccessToken($metaConfig['access_token'])
            ->setSenderId($metaConfig['whatsapp']['sender_id']);
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function setSenderId(string $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }

    public function setApiBaseUrl(string $apiBaseUrl): self
    {
        $this->apiBaseUrl = $apiBaseUrl;

        return $this;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setBusinessId(string $businessId): self
    {
        $this->businessId = $businessId;

        return $this;
    }

    public function createTemplate(string $name, string $categoryId, string $languageCode, array $components): Response
    {
        $response = Http::withToken($this->accessToken)
            ->throw()
            ->post("{$this->apiBaseUrl}/v{$this->version}/{$this->senderId}/message_templates", [
                'name' => $name,
                'language' => $languageCode,
                'category' => $categoryId,
                'components' => $components,
            ]);

        if ($response->failed()) {
            Log::error('WhatsApp Cloud API Error', [
                'response' => $response->json(),
                'status_code' => $response->status(),
            ]);

            throw new \Exception('Failed to communicate with WhatsApp Cloud API');
        }

        return $response;
    }
}
