# LIVV Stack
### Laravel, Inertia, Vue, Vuetify

## About LIVV

LIVV is a full stack web application framework built on top of the Laravel PHP framework, Inertia.js, Vue.js, and Vuetify.js. 

LIVV is designed to be a starting point for building web applications with a modern, hybrid frontend and a traditional server-side backend.

## Features

- Laravel 10.x, PHP 8.3
- Inertia.js for hybrid frontend
- Vue 3 with TypeScript
- Vuetify 3 using Material Design Icons
- Prettus Laravel Repositories for an abstraction layer between Models and Services
- Auth scaffolding (UI and backend) including login, registration, password reset, and email verification
- IDE helpers ready to go. Update with `sail composer ide-helpers`
- PHPStan ready to go `sail composer analyse`
- PHPUnit tests for auth scaffolding `sail artisan test`
- Laravel Pint for formatting: `sail composer format`
- ESLint for formatting and best practices: `npm run lint:fix`
- GitHub Action to run PHPUnit tests, check back end code formatting, check front end code formatting, and analyze code on push to main or PR to main

## Installation

1. Install [Docker](https://www.docker.com/get-started)
2. Run `bash scripts/init.sh`
3. Install npm dependencies
4. Run `npm run dev`

You can re-run init.sh at any time to reset the project.

## Vuetify Themes

A default light and dark theme is included
but feel free to add or update to make it your own in `resources/js/vuetify/available-themes.ts`

## License

LIVV Stack is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
