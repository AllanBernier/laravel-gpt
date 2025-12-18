# Laravel GPT

A powerful Laravel package for interacting with the ChatGPT API. This package provides a fluent, intuitive interface for making requests to OpenAI's ChatGPT API, with built-in support for function calling (tools).

## Features

- 🚀 Simple and fluent API for ChatGPT requests
- 🛠️ Built-in support for JSON Schema tools (function calling)
- 📦 Easy tool creation with artisan command
- 🔧 Type-safe tool implementation with `IChatTool` interface
- ⚡ Chainable methods for elegant code
- 🎯 Automatic tool execution based on ChatGPT's choices
- 🔄 Automatic retry logic for failed requests
- 📊 Usage tracking and token monitoring

## Requirements
- PHP >= 8.1
- Laravel >= 9.0 (supports Laravel 9, 10, 11, and 12)

## Installation

Install the package via Composer:

```bash
composer require allanbernier/laravel-gpt
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-gpt-config
```

Add your OpenAI API key to `.env`:
```env
OPENAI_API_KEY=your-api-key-here
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


### 3. Create a Tool

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
    public ?string $description = 'Finds a user by their name';

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
            ],
            'required' => ['name'],
        ];
    }

    public function invoke(array $args): mixed
    {
        return User::where('name', 'like', "%{$args['name']}%")->first();
    }
}
```

### 3. Use the Tool

```php
use AllanBernier\LaravelGpt\ChatGPT;
use App\ChatTools\FindUser;

$response = ChatGPT::new()
    ->tool(FindUser::class)
    ->prompt('Could you find the user with email john@example.com?')
    ->send();

$user = $response->tool->execute();
echo "Tool: {$response->tool->name}\n"; # find_user
echo "Arguments: " . json_encode($response->tool->args) . "\n"; # {"email": "john@example.com"}

```


## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
