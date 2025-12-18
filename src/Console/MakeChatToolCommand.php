<?php

namespace AllanBernier\LaravelGpt\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeChatToolCommand extends Command
{
    protected $signature = 'make:chatTool {name : The name of the tool class}';

    protected $description = 'Create a new ChatGPT tool class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $className = class_basename($name);
        $namespace = $this->getNamespace($name);

        $directory = app_path('ChatTools');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filePath = $directory . '/' . $className . '.php';

        if (File::exists($filePath)) {
            $this->error("Tool {$className} already exists!");
            return Command::FAILURE;
        }

        $stub = $this->getStub();
        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ class }}', $className, $stub);
        $stub = str_replace('{{ tool_name }}', $this->getToolName($className), $stub);

        File::put($filePath, $stub);

        $this->info("Tool {$className} created successfully at {$filePath}");

        return Command::SUCCESS;
    }

    protected function getNamespace(string $name): string
    {
        if (strpos($name, '\\') !== false) {
            return 'App\\ChatTools\\' . dirname(str_replace('/', '\\', $name));
        }

        return 'App\\ChatTools';
    }

    protected function getToolName(string $className): string
    {
        // Convert PascalCase to snake_case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace {{ namespace }};

use AllanBernier\LaravelGpt\ChatTool;

class {{ class }} extends ChatTool
{
    /**
     * The name of the function.
     * If not set, defaults to the class name in snake_case ({{ tool_name }}).
     */
    public ?string $name = null;

    /**
     * A description of what the function does.
     * Used by ChatGPT to decide when and how to call the function.
     */
    public ?string $description = null;

    /**
     * Whether to enable strict schema adherence.
     * Defaults to false if not set.
     */
    public ?bool $strict = null;

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                // Define your parameters here
                // Example:
                // 'param_name' => [
                //     'type' => 'string',
                //     'description' => 'Description of the parameter',
                // ],
            ],
            'required' => [],
        ];
    }

    public function invoke(array $args): mixed
    {
        // Implement your tool logic here
        // $args contains the arguments provided by ChatGPT
        
        return [
            'success' => true,
            'message' => 'Tool executed successfully',
        ];
    }
}
STUB;
    }
}
