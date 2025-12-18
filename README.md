# Laravel GPT

A powerful Laravel package for interacting with the ChatGPT API. This package provides a fluent, intuitive interface for making requests to OpenAI's ChatGPT API, with built-in support for function calling (tools).

## Features

- 🚀 Simple and fluent API for ChatGPT requests
- 🛠️ Built-in support for JSON Schema tools (function calling)
- 📦 Easy tool creation with artisan command
- 🔧 Type-safe tool implementation with `IChatTool` interface
- ⚡ Chainable methods for elegant code
- 🎯 Automatic tool execution based on ChatGPT's choices

## Requirements

- PHP >= 8.1
- Laravel >= 9.0

## Installation

Install the package via Composer:

```bash
composer require allanbernier/laravel-gpt
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-gpt-config
```

## Quick Start

### 1. Installation

```bash
composer require allanbernier/laravel-gpt
php artisan vendor:publish --tag=laravel-gpt-config
```

Add your OpenAI API key to `.env`:
```env
OPENAI_API_KEY=your-api-key-here
```

### 2. First Use

```php
use AllanBernier\LaravelGpt\ChatGPT;

$response = ChatGPT::new()
    ->prompt('Hello, how can I help you?')
    ->send();

echo $response->content;
```

📖 **For a complete integration guide, see [Integration Guide](docs/integration-guide.md)**

### 2. Create a Tool

Create a tool using the artisan command:

```bash
php artisan make:chatTool FindUser
```

This creates `app/ChatTools/FindUser.php`. Implement it:

```php
namespace App\ChatTools;

use AllanBernier\LaravelGpt\ChatTool;
use App\Models\User;

class FindUser extends ChatTool
{
    /**
     * The name of the function.
     * If not set, defaults to 'find_user' (class name in snake_case).
     */
    public ?string $name = null;

    /**
     * A description of what the function does.
     */
    public ?string $description = 'Finds a user by their name or email address';

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
                'name' => ['type' => 'string', 'description' => 'The name of the user to find'],
                'email' => ['type' => 'string', 'format' => 'email', 'description' => 'The email address of the user to find'],
            ],
            'required' => [],
        ];
    }

    public function invoke(array $args): mixed
    {
        $user = isset($args['email']) 
            ? User::where('email', $args['email'])->first()
            : User::where('name', 'like', "%{$args['name']}%")->first();
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        return [
            'success' => true,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ];
    }
}
```

### 3. Use the Tool with ChatGPT

```php
use AllanBernier\LaravelGpt\ChatGPT;
use App\ChatTools\FindUser;

$response = ChatGPT::new('gpt-3.5-turbo')
    ->tool(FindUser::class)
    ->prompt('Could you find the user with email john@example.com?')
    ->send();

if ($response->tool) {
    // Execute the tool chosen by ChatGPT
    $result = $response->tool->execute();
    
    // Access tool information
    echo "Tool: {$response->tool->name}\n";
    echo "Arguments: " . json_encode($response->tool->args) . "\n";
    echo "Result: " . json_encode($result) . "\n";
} else {
    echo $response->content;
}
```

### Basic Usage (Without Tools)

```php
use AllanBernier\LaravelGpt\ChatGPT;

$response = ChatGPT::new('gpt-3.5-turbo')
    ->prompt('Hello, what\'s going on?')
    ->send();

echo $response->content;
```

## Documentation

- [Integration Guide](docs/integration-guide.md) - **Guide complet d'intégration dans un projet réel**
- [Getting Started](docs/getting-started.md) - Installation and configuration
- [Basic Usage](docs/basic-usage.md) - Simple requests and responses
- [Tools](docs/tools.md) - Creating and using tools
- [Advanced Usage](docs/advanced-usage.md) - Advanced features and best practices
- [API Reference](docs/api-reference.md) - Complete API documentation
- [Configuration](docs/configuration.md) - Configuration options

## Testing

### Running Tests

Run all tests (unit and integration with mocks):
```bash
vendor/bin/phpunit
```

Run only unit tests:
```bash
vendor/bin/phpunit tests/Unit
```

Run only integration tests (with mocks):
```bash
vendor/bin/phpunit tests/Integration
```

### Real API Tests

The package includes optional tests that make real API calls to OpenAI. These tests are marked with `@group api` and require a valid API key.

**Important:** These tests will make real API calls and may incur costs.

To run real API tests:

1. Set your OpenAI API key:
```bash
export OPENAI_API_KEY=your-api-key-here
```

2. Run the API tests:
```bash
vendor/bin/phpunit --group api
```

Or run a specific API test:
```bash
vendor/bin/phpunit --group api tests/Integration/RealApiTest.php
```

The real API tests will be skipped automatically if `OPENAI_API_KEY` is not set.

## License

[Specify your license here]

## Contributing

[Contributing guidelines if applicable]
