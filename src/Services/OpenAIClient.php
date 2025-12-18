<?php

namespace AllanBernier\LaravelGpt\Services;

use AllanBernier\LaravelGpt\Exceptions\AuthenticationException;
use AllanBernier\LaravelGpt\Exceptions\ChatGPTException;
use AllanBernier\LaravelGpt\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Http;

class OpenAIClient
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $maxRetries;
    protected int $retryDelay;
    protected ?string $organization;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = $config['base_url'] ?? 'https://api.openai.com/v1';
        $this->timeout = $config['timeout'] ?? 30;
        $this->maxRetries = $config['max_retries'] ?? 3;
        $this->retryDelay = $config['retry_delay'] ?? 1;
        $this->organization = $config['organization'] ?? null;

        if (empty($this->apiKey)) {
            throw new AuthenticationException('OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file.');
        }
    }

    public function chat(array $payload): array
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getHeaders())
                    ->post("{$this->baseUrl}/chat/completions", $payload);

                if ($response->successful()) {
                    return $response->json();
                }

                // Don't retry on authentication or rate limit errors
                $statusCode = $response->status();
                if ($statusCode === 401 || $statusCode === 429) {
                    $this->handleErrorResponse($response);
                }

                // For other errors, check if we should retry
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    $this->handleErrorResponse($response);
                }

                sleep($this->retryDelay * $attempt);

            } catch (AuthenticationException|RateLimitException $e) {
                // Don't retry on auth/rate limit exceptions
                throw $e;
            } catch (\Exception $e) {
                $attempt++;

                if ($attempt >= $this->maxRetries) {
                    throw new ChatGPTException("Request failed after {$this->maxRetries} attempts: " . $e->getMessage(), 0, $e);
                }

                sleep($this->retryDelay * $attempt);
            }
        }

        throw new ChatGPTException('Request failed after all retry attempts.');
    }

    protected function getHeaders(): array
    {
        $headers = [
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ];

        if ($this->organization) {
            $headers['OpenAI-Organization'] = $this->organization;
        }

        return $headers;
    }

    protected function handleErrorResponse($response): void
    {
        $statusCode = $response->status();
        $errorBody = $response->json();

        $errorMessage = $errorBody['error']['message'] ?? 'Unknown error occurred';
        $errorCode = $errorBody['error']['code'] ?? null;

        switch ($statusCode) {
            case 401:
                throw new AuthenticationException("Authentication failed: {$errorMessage}");
            case 429:
                throw new RateLimitException("Rate limit exceeded: {$errorMessage}");
            default:
                throw new ChatGPTException("API request failed ({$statusCode}): {$errorMessage}", $statusCode);
        }
    }
}
