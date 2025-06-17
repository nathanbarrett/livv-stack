# App Overview
Put in the app overview here.

# Tech Stack And Best Practices

- **Laravel 12.x**: The latest version of the Laravel PHP framework.
    - Always add `declare(strict_types=1);` to the top of all new files
    - Use `sail artisan *` or `sail composer *` to run commands in the container
    - When passing data between services, repositories, models, or http responses always use the `Spatie\LaravelData\Data` class to ensure type safety.
    - Always create new entities with the command `sail artisan make:model MyModel -m -f` to create a model, migration, controller, and factory
    - Immediately after creating new entities, run `sail artisan make:repository MyModelRepository` to create a repository for the model.
    - When finishing up a new feature that contains migrations, run `sail artisan migrate` to run the migrations
    - When doing actions with entities, always use that entities repository instead of the model.
    - Always use PHP Enums for re-used values in the database or elsewhere
    - Always use Laravel's Http Client for making API calls to other services so we can use that service to mock the responses in tests
    - When using Laravel's Http Client, if we are doing more than one call to a service we need to make a custom macro and register that macro in the appropriate service provider
    - When using the Repository base class, use `$this->modelQuery()` to start a query builder, not `$this->query()`
    - Rules for new Models:
        - Always add `protected $guarded = ['id'];` to the model
        - If there is a JSON field it should always cast to a Spatie\LaravelData\Data object
        - Encrypt any sensitive fields in the model
        - Hide any sensitive fields in the model
    - The rules for models, repositories, and services are:
        - For actions directly related to an entity or actions that are mostly about an entity but might involve some other entities, use the primary entity's repository
        - Models cannot use other models or repositories or services
        - Models are for light data manipulation and validation on ONLY the model itself
        - Repositories can use other models, but not other repositories or services
        - Services can only use repositories
        - In controllers, it is acceptable to use methods from repositories and services
    - Rules for creating new routes:
        - Always create a custom form request to validate the request in the controller. Be as strict as possible with the validation rules.
        - Use custom form requests for authorization beyond auth checks, like checking if a user can update a specific entity. But do not do auth checks in the form request. That's for middleware.
        - Use resource words `index`, `show`, `create`, `store`, `edit`, `update`, and `destroy` for the route names. Use the same words for the controller methods.
        - Never use function-based middleware in routes. Always create proper middleware classes and register them with aliases.
        - Never define middleware inside controllers using $this->middleware(). Only define middleware in routes files or when updating stacks in service providers.
    - Rules for migrations:
        - Never create an enum field in a migration. Just do a string or varchar field and cast to a PHP enum
        - Consult database/database.dbml for the database schema. Update the database.dbml file when creating or updating migrations.
- **MySQL 8.0**: A popular open-source relational database management system.
- **Inertia.js**: A modern framework for building single-page applications (SPAs) using classic server-side routing and controllers.
    - See HandlesInertiaRequests for what you can access in your Vue components
- **Vue.js**: A progressive JavaScript framework for building user interfaces.
    - when crafting components and .vue files always use the `script setup lang="ts"` syntax
    - always create interfaces or types for parameters, returns, props, etc. if it's anything other than the basic types like string, number, boolean, etc.
    - if there is a small possibility that an interface or type will be used in multiple places, create a new file for it in the `types` directory. you can group similar types in files if that makes sense.
    - prefer types over enums i.e. `type MyType = 'one' | 'two' | 'three'` over `enum MyEnum { one, two, three }`
- **Vuetify.js**: A Material Design component framework for Vue.js.
    - Always prefer vuetify components for all basic UI elements such as display items or form inputs
- **TypeScript**: A superset of JavaScript that compiles to plain JavaScript.
    - Always use `tsc --noEmit` to check for type errors
    - Always use `npm run lint:fix` before committing if you have made any changes to TypeScript or Vue files
- **PHPUnit & Pest**: A testing framework for PHP.
    - Always add Pest tests for new routes, commands, and jobs
    - When writing new Pest tests, always give them a group name and only run that group name when testing your tests
    - Use `sail artisan test` to run the tests
- **Laravel Pint**: A code style fixer for PHP.
    - Always run `sail composer lint-fix` before committing if you have made any changes to PHP files

# Code Words
- "Final Checks" with capital F and C, means to do the following:
    - look at all tests. write new tests for all new routes, commands, and jobs. get rid of any tests that are not needed
    - run `sail artisan test`, fix any broken tests. re-test until all tests pass with a max of three tries to fix. If you can't fix it mark it as skipped and I will look at it later
    - run `sail composer analyse`, fix any phpstan errors that come up. re-run until all errors are fixed with a max of three tries to fix. If you can't fix it manually ignore it and I will look at it later
    - run `sail composer lint-fix`, this should auto fix all issue so no re-run is needed
    - run `npm run lint:fix`, this should auto fix all issue so no re-run is needed
- "Entity" usually means a thing that will have a table, model, and repository. It can also mean a thing that will have a table but no model or repository
- "Start a feature ...", means to pull the latest code from the main branch, create a new branch off of main with the name of the feature, and start working on the feature. For bugs prefix the branch name with `bugfix/`, for new features prefix the branch name with `feature/`, and for something that is more like a chore prefix the branch name with `chore/`. For example, if you are fixing a bug in the calendar feature, you would create a branch called `bugfix/calendar-fix`. If you are adding a new feature to the calendar, you would create a branch called `feature/calendar-new-feature`.
- "PR it", means to do "Final Checks" then commit your changes, push them to the remote branch. Then use the `gh` cli tool to create a pull request. The PR should be titled with the name of the feature, and the description should include a detailed list of all the changes made in the PR. The PR should be assigned to me, Nathan Barrett (username nathanbarrett).

# Development Rules

- **Never run build commands**: The user always has `npm run dev` running, so never run `npm run build`, `npm run dev`, or similar build commands

# Features

