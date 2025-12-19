# App Overview
Put app overview here.

# Tech Stack And Best Practices
IMPORTANT NOTES:
- for any `sail ...` commands they need to actually be `./vendor/bin/sail ...` commands
- Never run build commands. Always assume the site is running locally at `http://localhost` via `npm run dev` that I have running in another session.
- If you are navigating the web app and you need to be logged in, use the helper route `http://localhost/ai/login?email=nathan.barrett%40gmail.com` which will log you into my account
- **Laravel 12.x**: The latest version of the Laravel PHP framework.
    - Use the MCP server `laravel-boost` to help with development
- **MySQL 8.0**: A popular open-source relational database management system.
    - current database schema can be found at `database/database.dbml`. keep it up to date if you make any changes
    - If you need to query the database you can do so by either running `sail artisan tinker --execute="echo \App\Models\SomeModel::query()->..."` or raw SQL like `sail artisan tinker --execute="print_r(DB::select('select count(*) as count from users'));"`
- **Inertia.js**: A modern framework for building single-page applications (SPAs) using classic server-side routing and controllers.
    - See HandlesInertiaRequests for what you can access in your Vue components
- **Vue.js**: A progressive JavaScript framework for building user interfaces.
    - Always run `npm run tsc` to check for Vue errors after writing any Vue code
    - When doing imports in .vue files, always use the `@js/*` alias for the `resources/js/*` directory
    - do not import compiler macros like `import { defineComponent } from 'vue'` in your .vue files, instead use the `script setup lang="ts"` syntax
    - when crafting components and .vue files always use the `script setup lang="ts"` syntax
    - always create interfaces or types for parameters, returns, props, etc. if it's anything other than the basic types like string, number, boolean, etc.
    - if there is a small possibility that an interface or type will be used in multiple places, create a new file for it in the `types` directory. you can group similar types in files if that makes sense.
    - prefer types over enums i.e. `type MyType = 'one' | 'two' | 'three'` over `enum MyEnum { one, two, three }`
- **Vuetify.js**: A Material Design component framework for Vue.js.
    - ALWAYS prefer vuetify components for all basic UI elements such as display items or form inputs. You have a vuetify mcp server at your disposal.
- **TypeScript**: A superset of JavaScript that compiles to plain JavaScript.
    - Always use `npm run tsc` to check for type errors after writing any typescript in .vue or .ts files.
    - Always use `npm run lint:fix` after completing a task if you have made any changes to TypeScript or Vue files. fix any non-auto-fixable issues.
- **PHPUnit & Pest**: A testing framework for PHP.
    - Always add Pest tests for new routes, commands, and jobs
    - When writing new Pest tests, always give them a group name and only run that group name when testing your tests
    - Use `sail artisan test` to run the tests
- **Laravel Pint**: A code style fixer for PHP.
    - Always run `sail composer lint-fix` after completing a task if you have made any changes to PHP files. Fix any non-auto-fixable issues.

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

# Named Tasks
NOTE: Ignore all named tasks below unless I specifically ask you to do one of them. Each task will have a list of checkboxes that you need to complete.
Mark each checkbox as complete when you finish the task. If you can't complete a task, mark it as skipped and the reason why and I will look at it later.
Add any additional tasks that you think are necessary to complete the task that I might have missed.



<!-- DYNAMIC CONTEXT MCP GUIDELINES START -->

<CRITICAL_INSTRUCTION>

## DYNAMIC CONTEXT INSTRUCTIONS

This project uses dynamic context mcp to deliver "just in time" context for files that you are about to read, create, or edit.

**CRITICAL GUIDANCE**

- Before you read, create, or edit a file, check for dynamic context by requesting the `dynamic-context.get_context_for_file` tool with the file path as input.
- If dynamic context is available, read it carefully to understand important details about how to work with that file.

</CRITICAL_INSTRUCTION>

<!-- DYNAMIC CONTEXT MCP GUIDELINES END -->