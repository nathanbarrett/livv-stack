# Installation

Getting started with Prism is straightforward.

## Requirements

Before installing, make sure your project meets these requirements:

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Step 1: Composer Installation

> **TIP:** Prism is actively evolving. To prevent unexpected issues from breaking changes, we strongly recommend pinning your installation to a specific version. Example: `"prism-php/prism": "^0.3.0"`.

Add Prism to your project using Composer:

```bash
composer require prism-php/prism
```

This command will download Prism and its dependencies into your project.

## Step 2: Publish the Configuration

Prism comes with a configuration file that you'll want to customize. Publish it to your config directory:

```bash
php artisan vendor:publish --tag=prism-config
```

This will create a new file at `config/prism.php`. See the [Configuration](configuration.md) section for details on configuring Prism.
