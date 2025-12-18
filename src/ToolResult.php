<?php

namespace AllanBernier\LaravelGpt;

use AllanBernier\LaravelGpt\Contracts\IChatTool;

class ToolResult
{
    public string $name;
    public array $args;
    protected string $toolClass;

    public function __construct(string $name, array $args, string $toolClass)
    {
        $this->name = $name;
        $this->args = $args;
        $this->toolClass = $toolClass;
    }

    public function execute(): mixed
    {
        if (!class_exists($this->toolClass, true)) {
            throw new \RuntimeException("Tool class {$this->toolClass} does not exist.");
        }

        $tool = app($this->toolClass);

        if (!$tool instanceof IChatTool) {
            throw new \RuntimeException("Class {$this->toolClass} does not implement IChatTool interface.");
        }

        return $tool->invoke($this->args);
    }
}
