<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin\Payum;

final class Api
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiToken;

    public function __construct(string $apiKey, string $apiToken)
    {
        $this->apiKey = $apiKey;
        $this->apiToken = $apiToken;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }
}
