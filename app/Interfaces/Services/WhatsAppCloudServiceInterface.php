<?php

namespace App\Interfaces\Services;

use Illuminate\Http\Client\Response;

interface WhatsAppCloudServiceInterface
{
    public function setApiBaseUrl(string $apiBaseUrl): self;
    public function setAccessToken(string $accessToken): self;
    public function setVersion(string $version): self;
    public function setSenderId(string $senderId): self;
    public function setBusinessId(string $businessId): self;
    public function createTemplate(string $name, string $categoryId, string $languageCode, array $components): array;
    public function getTemplate(string $name, ?string $templateId = null): array;

    public function sendTemplateMessage(
        string $recipient,
        string $templateName,
        string $languageCode,
        array $bodyParams = [],
        ?string $headerParam = null,
    ): array;
}
