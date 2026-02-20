# Laravel Postman Collection Generator

A smart Postman collection generator for Laravel 11.x that automatically scans routes, extracts metadata from attributes/docblocks, and generates realistic request body examples from FormRequests.

## Features

- **Automated Scanning**: Automatically finds all API routes.
- **Metadata Extraction**: Derives request names, descriptions, and folders from `#[PostmanMeta]` attributes or DocBlocks.
- **Body Example Generation**: Parses FormRequest validation rules (including nested and wildcard rules) to create non-empty JSON/form-data bodies.
- **Response Capture**: Optionally hits your local endpoints to capture live response examples.
- **Smart Grouping**: Groups requests into folders based on URI segments or Controller names.
- **Auth Detection**: Automatically detects Bearer authentication from middlewares.

## Installation

```bash
composer require laravelgenerators/postman-generator --dev
```

The package uses Laravel's auto-discovery, so it's ready to use immediately.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=postman-generator-config
```

### Key Options

In `config/postman-generator.php`:
- `collection_name`: The name of your imported collection.
- `base_url`: Defaults to your `APP_URL`, used as `{{base_url}}` variable.
- `auto_examples`: Set to `true` (default) to generate bodies from FormRequests.
- `generate_responses`: Set to `true` to capture live responses (local-only for safety).

### Local Response Capture
If `generate_responses` is enabled, define a token in your `.env` for authenticated requests:
```env
POSTMAN_GENERATOR_TOKEN=your-local-dev-bearer-token
```

## Usage

Generate the collection JSON:

```bash
php artisan postman:generate
```

Override output path:

```bash
php artisan postman:generate --output=path/to/my-collection.json
```

## Annotating Routes

Use the `#[PostmanMeta]` attribute for precise control:

```php
use LaravelGenerators\PostmanGenerator\Attributes\PostmanMeta;

class OrderController extends Controller
{
    #[PostmanMeta(
        name: 'List All Orders', 
        folder: 'Order Management', 
        description: 'Returns a paginated list of orders'
    )]
    public function index() { ... }
}
```

Or use DocBlock tags:

```php
/**
 * @postman-name List All Orders
 * @postman-folder Order Management
 */
public function index() { ... }
```

## License

The MIT License (MIT).
