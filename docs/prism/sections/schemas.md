# Schemas

Schemas are the blueprints that help you define the shape of your data in Prism. They're used for tool parameters and structured outputs.

## Quick Start

> **IMPORTANT:** When using schemas for structured output with providers like OpenAI (especially in strict mode), the root schema should be an `ObjectSchema`. Other schema types can only be used as properties within an ObjectSchema.

```php
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

$userSchema = new ObjectSchema(
    name: 'user',
    description: 'A user profile with their hobbies',
    properties: [
        new StringSchema('name', 'The user\'s full name'),
        new ArraySchema(
            name: 'hobbies',
            description: 'The user\'s list of hobbies',
            items: new ObjectSchema(
                name: 'hobby',
                description: 'A detailed hobby entry',
                properties: [
                    new StringSchema('name', 'The name of the hobby'),
                    new StringSchema('description', 'A brief description of the hobby'),
                ],
                requiredFields: ['name', 'description']
            )
        ),
    ],
    requiredFields: ['name', 'hobbies']
);
```

## Available Schema Types

### StringSchema

For text values of any length:

```php
use Prism\Prism\Schema\StringSchema;

$nameSchema = new StringSchema(
    name: 'full_name',
    description: 'The user\'s full name including first and last name'
);
```

### NumberSchema

Handles both integers and floating-point numbers:

```php
use Prism\Prism\Schema\NumberSchema;

$ageSchema = new NumberSchema(
    name: 'age',
    description: 'The user\'s age in years'
);
```

### BooleanSchema

For simple true/false values:

```php
use Prism\Prism\Schema\BooleanSchema;

$activeSchema = new BooleanSchema(
    name: 'is_active',
    description: 'Whether the user account is active'
);
```

### ArraySchema

For lists of items where each item follows a specific schema:

```php
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\StringSchema;

$tagsSchema = new ArraySchema(
    name: 'tags',
    description: 'List of tags associated with the post',
    items: new StringSchema('tag', 'A single tag')
);
```

### EnumSchema

Restrict values to a specific set of options:

```php
use Prism\Prism\Schema\EnumSchema;

$statusSchema = new EnumSchema(
    name: 'status',
    description: 'The current status of the post',
    options: ['draft', 'published', 'archived']
);
```

### ObjectSchema

For complex, nested data structures:

```php
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;

$profileSchema = new ObjectSchema(
    name: 'profile',
    description: 'A user\'s public profile information',
    properties: [
        new StringSchema('username', 'The unique username'),
        new StringSchema('bio', 'A short biography'),
        new NumberSchema('joined_year', 'Year the user joined'),
    ],
    requiredFields: ['username']
);
```

### AnyOfSchema

For flexible data that can match one of several schemas:

```php
use Prism\Prism\Schema\AnyOfSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;

// Simple: A value that can be either a string or number
$flexibleValueSchema = new AnyOfSchema(
    schemas: [
        new StringSchema('text', 'A text value'),
        new NumberSchema('number', 'A numeric value'),
    ],
    name: 'flexible_value',
    description: 'A value that can be either text or numeric'
);

// Complex: Different content types
$contentSchema = new AnyOfSchema(
    schemas: [
        new ObjectSchema(
            name: 'article',
            description: 'A blog article',
            properties: [
                new StringSchema('title', 'Article title'),
                new StringSchema('content', 'Article content'),
            ],
            requiredFields: ['title', 'content']
        ),
        new ObjectSchema(
            name: 'image',
            description: 'An image post',
            properties: [
                new StringSchema('url', 'Image URL'),
                new StringSchema('caption', 'Image caption'),
            ],
            requiredFields: ['url']
        ),
    ],
    name: 'content',
    description: 'Content that can be either an article or an image'
);
```

**Provider Support for AnyOfSchema:**

| Provider | anyOf Support | Notes |
|----------|--------------|-------|
| OpenAI | ✓ Full | Works with structured outputs API |
| Gemini | ✓ Full | Enhanced JSON Schema support |
| Anthropic | ✗ Not supported | Use alternative schema design patterns |

## Nullable Fields

Make any schema nullable by setting the `nullable` parameter:

```php
$bioSchema = new StringSchema(
    name: 'bio',
    description: 'Optional user biography',
    nullable: true
);
```

> **NOTE:** When using OpenAI in strict mode, all fields must be marked as required, so optional fields must be marked as nullable.

## Required vs Nullable Fields

Understanding the difference is crucial:

### Required Fields

Specified at the object level using `requiredFields`. They indicate which properties must be present:

```php
$userSchema = new ObjectSchema(
    name: 'user',
    description: 'User profile',
    properties: [
        new StringSchema('email', 'Primary email address'),
        new StringSchema('name', 'User\'s full name'),
        new StringSchema('bio', 'User biography', nullable: true),
    ],
    requiredFields: ['email', 'name', 'bio'] // all fields must be present
);
```

### Nullable Fields

Specified at the individual field level. They indicate that a field can contain a `null` value:

```php
// bio must be present, but can be null
new StringSchema('bio', 'User biography', nullable: true)
```

### Common Patterns

```php
// Required and Non-nullable (most strict)
new StringSchema('email', 'Primary email', nullable: false);
// requiredFields: ['email']

// Required but Nullable (must be present, can be null)
new StringSchema('bio', 'User bio', nullable: true);
// requiredFields: ['bio']

// Optional and Non-nullable (can be omitted, but if present cannot be null)
new StringSchema('phone', 'Phone number', nullable: false);
// requiredFields: []

// Optional and Nullable (most permissive)
new StringSchema('website', 'Personal website', nullable: true);
// requiredFields: []
```

### Provider Considerations (OpenAI strict mode)

```php
// For OpenAI strict mode:
// - All fields should be required
// - Use nullable: true for optional fields
$userSchema = new ObjectSchema(
    name: 'user',
    description: 'User profile',
    properties: [
        new StringSchema('email', 'Required email address'),
        new StringSchema('bio', 'Optional biography', nullable: true),
    ],
    requiredFields: ['email', 'bio'] // Note: bio is required but nullable
);
```

## Best Practices

### 1. Clear Descriptions

```php
// ❌ Not helpful
new StringSchema('name', 'the name');

// ✓ Much better
new StringSchema('name', 'The user\'s display name (2-50 characters)');
```

### 2. Thoughtful Required Fields

Only mark fields as required if they're truly necessary:

```php
new ObjectSchema(
    name: 'user',
    description: 'User profile',
    properties: [
        new StringSchema('email', 'Primary email address'),
        new StringSchema('phone', 'Optional phone number', nullable: true),
    ],
    requiredFields: ['email']
);
```

### 3. Nested Organization

Keep schemas organized when dealing with complex structures:

```php
// Define child schemas first
$addressSchema = new ObjectSchema(/*...*/);
$contactSchema = new ObjectSchema(/*...*/);

// Then use them in your parent schema
$userSchema = new ObjectSchema(
    name: 'user',
    description: 'Complete user profile',
    properties: [$addressSchema, $contactSchema]
);
```

> **NOTE:** While schemas help define the structure of your data, Prism doesn't currently validate the data against these schemas. Schema validation is planned for a future release.
