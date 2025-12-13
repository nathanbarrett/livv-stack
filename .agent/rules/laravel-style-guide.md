---
trigger: glob
globs: *.php
---

# Laravel & PHP Guidelines for AI Code Assistants

This file contains Laravel and PHP coding standards optimized for AI code assistants like Claude Code, GitHub Copilot, and Cursor. These guidelines are derived from Spatie's comprehensive Laravel & PHP standards.

## Core Laravel Principle

**Follow Laravel conventions first.** If Laravel has a documented way to do something, use it. Only deviate when you have a clear justification.

# Important

-   Never use namespaced classes inside of functional code like `public function index(): \Illuminate\Response\JsonResponse` always import instead
-   Always have `declare(strict_types=1);` as the first line of code after `<?php`

## Services vs Actions vs Models - when to use

-   for every feature break up each core piece of functionality into an action class i.e. `UpdatePostAction` in `app/Actions/Posts/UpdatePostAction.php`
-   every action class should have a public `handle` method. use the constructor to DI anything else you need from the container
-   use service classes only when a piece of functionality in a controller, job, command etc uses three or more Action classes to complete
-   You can use services and actions anywhere but only services and actions can use eloquent models unless you have to. try to keep all model interactions within a service or action
-   inspect actions and reuse them as needed

## Commands to run after PHP updates

-   run these only at the end of every successful task
-   run `sail composer lint-fix` , fix any that the linter could not fix
-   run `sail composer analyse` , fix any errors from that

## PHP Standards

-   Follow PSR-1, PSR-2, and PSR-12
-   every PHP file you work on, created or not, needs to have a `declare(strict_types=1);` as the first thing after `<?php`
-   Use camelCase for non-public-facing strings
-   Use short nullable notation: `?string` not `string|null`
-   Always specify `void` return types when methods return nothing

## Class Structure

-   Use typed properties, not docblocks:
-   Constructor property promotion when all properties can be promoted:
-   One trait per line:

## Type Declarations & Docblocks

-   Use typed properties over docblocks
-   Specify return types including `void`
-   Use short nullable syntax: `?Type` not `Type|null`
-   Document iterables with generics:
    ```php
    /** @return Collection<int, User> */
    public function getUsers(): Collection
    ```

### Docblock Rules

-   Don't use docblocks for fully type-hinted methods (unless description needed)
-   **Always import classnames in docblocks** - never use fully qualified names:
    ```php
    use \Spatie\Url\Url;
    /** @return Url */
    ```
-   Use one-line docblocks when possible: `/** @var string */`
-   Most common type should be first in multi-type docblocks:
    ```php
    /** @var Collection|SomeWeirdVendor\Collection */
    ```
-   If one parameter needs docblock, add docblocks for all parameters
-   For iterables, always specify key and value types:
    ```php
    /**
     * @param array<int, MyObject> $myArray
     * @param int $typedArgument
     */
    function someFunction(array $myArray, int $typedArgument) {}
    ```
-   Use array shape notation for fixed keys, put each key on it's own line:
    ```php
    /** @return array{
       first: SomeClass,
       second: SomeClass
    } */
    ```

## Control Flow

-   **Happy path last**: Handle error conditions first, success case last
-   **Avoid else**: Use early returns instead of nested conditions
-   **Separate conditions**: Prefer multiple if statements over compound conditions
-   **Always use curly brackets** even for single statements
-   **Ternary operators**: Each part on own line unless very short

```php
// Happy path last
if (! $user) {
    return null;
}

if (! $user->isActive()) {
    return null;
}

// Process active user...

// Short ternary
$name = $isFoo ? 'foo' : 'bar';

// Multi-line ternary
$result = $object instanceof Model ?
    $object->name :
    'A default value';

// Ternary instead of else
$condition
    ? $this->doSomething()
    : $this->doSomethingElse();
```

## Laravel Conventions

-   Use `config()` helper, avoid `env()` outside config files

## Strings & Formatting

-   **String interpolation** over concatenation:

## Enums

-   Use PascalCase for enum values

## Response Codes

-   Instead of using integers for response codes use the constants defined on `Illuminate\Http\Response` like `Response::HTTP_CREATED`

## Comments

-   **Avoid comments** - write expressive code instead
-   When needed, use proper formatting:

    ```php
    // Single line with space after //

    /*
     * Multi-line blocks start with single *
     */
    ```

-   Refactor comments into descriptive function names

## Whitespace

-   Add blank lines between statements for readability
-   Exception: sequences of equivalent single-line operations
-   No extra empty lines between `{}` brackets
-   Let code "breathe" - avoid cramped formatting

## Translations

-   Use `__()` function over `@lang`:

## Quick Reference

### Naming Conventions

-   **Classes**: PascalCase (`UserController`, `OrderStatus`)
-   **Methods/Variables**: camelCase (`getUserName`, `$firstName`)
-   **Routes**: kebab-case (`/open-source`, `/user-profile`)
-   **Config files**: kebab-case (`pdf-generator.php`)
-   **Config keys**: snake_case (`chrome_path`)
-   **Artisan commands**: kebab-case (`php artisan delete-old-records`)

### File Structure

-   Controllers: plural resource name + `Controller` (`PostsController`)
-   Views: camelCase (`openSource.blade.php`)
-   Jobs: action-based (`CreateUser`, `SendEmailNotification`)
-   Events: tense-based (`UserRegistering`, `UserRegistered`)
-   Listeners: action + `Listener` suffix (`SendInvitationMailListener`)
-   Commands: action + `Command` suffix (`PublishScheduledPostsCommand`)
-   Mailables: purpose + `Mail` suffix (`AccountActivatedMail`)
-   Resources/Transformers: plural + `Resource`/`Transformer` (`UsersResource`)
-   Enums: descriptive name, no prefix (`OrderStatus`, `BookingType`)

### Code Quality Reminders

#### PHP

-   Use typed properties over docblocks
-   Prefer early returns over nested if/else
-   Use constructor property promotion when all properties can be promoted
-   Avoid `else` statements when possible
-   Use string interpolation over concatenation
-   Always use curly braces for control structures
