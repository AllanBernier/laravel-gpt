<?php

namespace AllanBernier\LaravelGpt;

use AllanBernier\LaravelGpt\ToolResult;

class Response
{
    public string $content;
    public string $model;
    public object $usage;
    public array $raw;
    public ?ToolResult $tool = null;

    public function __construct(array $apiResponse, array $toolMapping = [])
    {
        $this->raw = $apiResponse;
        $this->model = $apiResponse['model'] ?? '';
        
        $choice = $apiResponse['choices'][0] ?? [];
        $message = $choice['message'] ?? [];

        // Check if a tool was called
        if (isset($message['tool_calls']) && !empty($message['tool_calls'])) {
            $toolCall = $message['tool_calls'][0];
            $toolName = $toolCall['function']['name'] ?? '';
            $toolArgsJson = $toolCall['function']['arguments'] ?? '{}';
            $toolArgs = json_decode($toolArgsJson, true);
            
            // Ensure toolArgs is an array (json_decode can return null for invalid JSON)
            if (!is_array($toolArgs)) {
                $toolArgs = [];
            }

            // Find the tool class from the mapping
            $toolClass = $toolMapping[$toolName] ?? null;

            if ($toolClass) {
                $this->tool = new ToolResult($toolName, $toolArgs, $toolClass);
            }
        }

        // Set content (may be empty if tool was called)
        $this->content = $message['content'] ?? '';

        // Set usage information
        $this->usage = (object) [
            'promptTokens' => $apiResponse['usage']['prompt_tokens'] ?? 0,
            'completionTokens' => $apiResponse['usage']['completion_tokens'] ?? 0,
            'totalTokens' => $apiResponse['usage']['total_tokens'] ?? 0,
        ];
    }
}
