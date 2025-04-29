<?php

namespace App\Interfaces\Services;

interface WhatsAppCloudServiceInterface
{
    public function setApiBaseUrl(string $apiBaseUrl): self;
    public function setAccessToken(string $accessToken): self;
    public function setVersion(string $version): self;
    public function setSenderId(string $senderId): self;
}
