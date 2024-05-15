# LIVV Stack
### Laravel, Inertia, Vue, Vuetify

## About LIVV

LIVV is a full stack web application framework built on top of the Laravel PHP framework, Inertia.js, Vue.js, and Vuetify.js.

LIVV is designed to be a starting point for building web applications with a modern, hybrid frontend and a traditional server-side backend.

## Features

- Laravel 11.x, PHP 8.3
- Inertia.js for hybrid frontend
- Vue 3 with TypeScript
- Vuetify 3 using Material Design Icons
- Built in front end form validation
- Helper functions for quick messages (toast) to your users
- Helper function for quick confirmation dialogs
- Repositories for an abstraction layer between Models and Services
- Auth scaffolding (UI and backend) including login, registration, password reset, and email verification
- IDE helpers ready to go. Update with `sail composer ide-helpers`
- PHPStan ready to go `sail composer analyse`
- PHPUnit tests for auth scaffolding `sail artisan test`
- Laravel Pint for formatting: `sail composer format`
- ESLint for formatting and best practices: `npm run lint:fix`
- GitHub Action to run PHPUnit tests, check back end code formatting, check front end code formatting, and analyze code on push to main or PR to main

## Installation

Prerequisits:
- [Docker](https://www.docker.com/get-started)
- [Node >=v20](https://nodejs.org/en/download)
- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

### Easiest way if you are using GitHub
- Click on `Use this template` button in the top right and create your own repo from this file structure
- Clone your new repository locally
- run `bash scripts/init.sh`

### Or install with Git
1. Run `git clone git@github.com:nathanbarrett/livv-stack.git my-app` (replace my-app)
2. `cd my-app`
3. `bash scripts/init.sh`
4. Visit http://localhost

OR (faster way)

Go to your projects directory and run 👇 (replace `my-app` with your desired project name)
```shell
APP_NAME=my-app && git clone git@github.com:nathanbarrett/livv-stack.git $APP_NAME && cd $APP_NAME && bash scripts/init.sh
```

You can re-run init.sh at any time to rebuild the project.

## Github Actions
Rename `.github/workflows/laravel_ci.yml.disabled` to `.github/workflows/laravel_ci.yml` to enable the GitHub Action.

## Vuetify Themes

A default light and dark theme is included
but feel free to add or update to make it your own in `resources/js/vuetify/available-themes.ts`

## License

LIVV Stack is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
