<?php

namespace AllanBernier\LaravelGpt;

use AllanBernier\LaravelGpt\Contracts\IChatTool;
use AllanBernier\LaravelGpt\Services\OpenAIClient;

class ChatGPT
{
    protected string $model;
    protected ?string $prompt = null;
    protected array $tools = [];
    protected array $toolMapping = []; // Maps tool name to class
    protected array $messages = [];
    protected OpenAIClient $client;

    public function __construct(?string $model = null)
    {
        $config = config('laravel-gpt');
        $this->model = $model ?? $config['default_model'] ?? 'gpt-3.5-turbo';
        $this->client = new OpenAIClient($config);
    }

    public static function new(?string $model = null): self
    {
        return new self($model);
    }

    public function prompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    public function tool(string $toolClass): self
    {
        if (!class_exists($toolClass, true)) {
            throw new \InvalidArgumentException("Tool class {$toolClass} does not exist.");
        }

        $tool = app($toolClass);

        if (!$tool instanceof IChatTool) {
            throw new \InvalidArgumentException("Class {$toolClass} does not implement IChatTool interface.");
        }

        $this->tools[] = $tool;
        $toolName = $this->getToolName($tool);
        $this->toolMapping[$toolName] = $toolClass;

        return $this;
    }

    protected function getToolName(IChatTool $tool): string
    {
        // If tool is a ChatTool instance, use getName()
        if ($tool instanceof \AllanBernier\LaravelGpt\ChatTool) {
            return $tool->getName();
        }

        // Fallback: try to access name property directly
        if (property_exists($tool, 'name') && $tool->name !== null) {
            return $tool->name;
        }

        // Last resort: use class name in snake_case
        $className = class_basename(get_class($tool));
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    public function tools(array $toolClasses): self
    {
        foreach ($toolClasses as $toolClass) {
            $this->tool($toolClass);
        }

        return $this;
    }

    public function messages(array $messages): self
    {
        $this->messages = $messages;
        return $this;
    }

    public function send(): Response
    {
        $payload = $this->buildPayload();
        $apiResponse = $this->client->chat($payload);

        return new Response($apiResponse, $this->toolMapping);
    }

    protected function buildPayload(): array
    {
        $payload = [
            'model' => $this->model,
            'messages' => $this->buildMessages(),
        ];

        // Add tools if any
        if (!empty($this->tools)) {
            $payload['tools'] = $this->buildTools();
        }

        return $payload;
    }

    protected function buildMessages(): array
    {
        $messages = $this->messages;

        // Add the prompt as a user message if provided
        if ($this->prompt !== null) {
            $messages[] = [
                'role' => 'user',
                'content' => $this->prompt,
            ];
        }

        return $messages;
    }

    protected function buildTools(): array
    {
        $tools = [];

        foreach ($this->tools as $tool) {
            $name = $this->getToolName($tool);
            $description = $this->getToolDescription($tool);
            $strict = $this->getToolStrict($tool);

            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $name,
                    'description' => $description,
                    'parameters' => $tool->parameters(),
                    'strict' => $strict,
                ],
            ];
        }

        return $tools;
    }

    protected function getToolDescription(IChatTool $tool): string
    {
        // If tool is a ChatTool instance, use getDescription()
        if ($tool instanceof \AllanBernier\LaravelGpt\ChatTool) {
            return $tool->getDescription();
        }

        // Fallback: try to access description property directly
        if (property_exists($tool, 'description') && $tool->description !== null) {
            return $tool->description;
        }

        return '';
    }

    protected function getToolStrict(IChatTool $tool): bool
    {
        // If tool is a ChatTool instance, use getStrict()
        if ($tool instanceof \AllanBernier\LaravelGpt\ChatTool) {
            return $tool->getStrict();
        }

        // Fallback: try to access strict property directly
        if (property_exists($tool, 'strict') && $tool->strict !== null) {
            return $tool->strict;
        }

        return false;
    }
}
