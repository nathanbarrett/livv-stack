---
trigger: glob
globs: resources/js/**/*.{ts,vue}
---

-   Always assume that `npm run dev` is already running somewhere
-   Use mcp server `laravel-boost` for best practices when working with InertiaJS
-   If editing UI, at the end of your work always visually look at your page using browser mcp resources and check browser logs for any errors
-   all user dashboard pages must extend the same layout in the same fashion
-   Uses InertiaJS. See HandlesInertiaRequests for what you can access in your Vue components
-   Always run `npm run tsc` to check for Vue errors after writing any Vue code
-   when giving feedback to the user in the UI the helper tools in `resources/js/common/snackbar.ts` are preferred
-   When doing imports in .vue files, always use the `@js/*` alias for the `resources/js/*` directory
-   do not import compiler macros like `import { defineComponent } from 'vue'` in your .vue files, instead use the `<script setup lang="ts">` syntax
-   always create interfaces or types for parameters, returns, props, etc. if it's anything other than the basic types like string, number, boolean, etc.
-   if there is a small possibility that an interface or type will be used in multiple places, create a new file for it in the `types` directory. you can group similar types in files if that makes sense.
-   prefer types over enums i.e. `type MyType = 'one' | 'two' | 'three'` over `enum MyEnum { one, two, three }`

-   ALWAYS prefer vuetify components for all basic UI elements such as display items or form inputs. You have a vuetify mcp server at your disposal.
-   Always use `npm run tsc` to check for type errors after writing any typescript in .vue or .ts files.
-   Always use `npm run lint:fix` after completing a task if you have made any changes to TypeScript or Vue files. fix any non-auto-fixable issues.
