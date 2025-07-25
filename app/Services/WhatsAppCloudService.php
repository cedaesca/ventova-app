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
            ->setSenderId($metaConfig['whatsapp']['sender_id'])
            ->setBusinessId($metaConfig['whatsapp']['business_id']);
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

    public function createTemplate(string $name, string $categoryId, string $languageCode, array $components): array
    {
        $params = [
            'name' => $name,
            'language' => $languageCode,
            'category' => $categoryId,
            'components' => $components,
        ];

        $response = Http::withToken($this->accessToken)
            ->throw()
            ->post("{$this->apiBaseUrl}/v{$this->version}/{$this->businessId}/message_templates", $params);

        return json_decode($response->body(), true);
    }

    public function getTemplate(string $name, ?string $templateId = null): array
    {
        $queryParams = "name={$name}";

        if ($templateId) {
            $queryParams .= "&hsm_id={$templateId}";
        }

        $response = Http::withToken($this->accessToken)
            ->throw()
            ->get("{$this->apiBaseUrl}/v{$this->version}/{$this->businessId}/message_templates?{$queryParams}");

        return json_decode($response->body(), true);
    }

    public function sendTemplateMessage(
        string $recipient,
        string $templateName,
        string $languageCode,
        array $bodyParams = [],
        ?string $headerParam = null,
    ): array {
        $params = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (!empty($bodyParams)) {
            $params['template']['components'][] = [
                'type' => 'body',
                'parameters' => array_map(fn($param) => ['type' => 'text', 'text' => $param], $bodyParams),
            ];
        }

        if (!is_null($headerParam)) {
            $params['template']['components'][] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'text',
                    'text' => $headerParam,
                ]],
            ];
        }

        $response = Http::withToken($this->accessToken)
            ->throw()
            ->post("{$this->apiBaseUrl}/v{$this->version}/{$this->senderId}/messages", $params);

        return json_decode($response->body(), true);
    }
}
