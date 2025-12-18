<?php

namespace AllanBernier\LaravelGpt\Contracts;

interface IChatTool
{
    /**
     * Returns the parameters the function accepts, described as a JSON Schema object.
     */
    public function parameters(): array;

    /**
     * Executes when ChatGPT chooses to call this tool.
     * 
     * @param array $args The arguments determined by ChatGPT based on the conversation
     * @return mixed The result of the tool execution
     */
    public function invoke(array $args): mixed;
}
