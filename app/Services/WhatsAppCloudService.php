<?php

namespace App\Services;

use App\Interfaces\Services\WhatsAppCloudServiceInterface;

class WhatsAppCloudService implements WhatsAppCloudServiceInterface
{
    private string $version;
    private string $senderId;
    private string $apiBaseUrl;
    private string $accessToken;

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
}
