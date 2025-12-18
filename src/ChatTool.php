<?php

namespace AllanBernier\LaravelGpt;

use AllanBernier\LaravelGpt\Contracts\IChatTool;

abstract class ChatTool implements IChatTool
{
    /**
     * The name of the function.
     * Must be maximum 64 characters and only contain: a-z, A-Z, 0-9, underscores, and dashes.
     * If not set, defaults to the class name in snake_case.
     */
    public ?string $name = null;

    /**
     * A description of what the function does.
     * Used by ChatGPT to decide when and how to call the function.
     */
    public ?string $description = null;

    /**
     * Whether to enable strict schema adherence when generating the function call.
     * If set to true, the model will follow the exact schema defined in the parameters field.
     * Defaults to false if not set.
     */
    public ?bool $strict = null;

    public function __construct()
    {
        // Set default name from class name if not provided
        if ($this->name === null) {
            $this->name = $this->getDefaultName();
        }

        // Set default strict to false if not provided
        if ($this->strict === null) {
            $this->strict = false;
        }
    }

    /**
     * Get the default name from the class name (PascalCase to snake_case).
     */
    protected function getDefaultName(): string
    {
        $className = class_basename(static::class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Get the name of the tool.
     */
    public function getName(): string
    {
        return $this->name ?? $this->getDefaultName();
    }

    /**
     * Get the description of the tool.
     */
    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    /**
     * Get the strict mode setting.
     */
    public function getStrict(): bool
    {
        return $this->strict ?? false;
    }
}
