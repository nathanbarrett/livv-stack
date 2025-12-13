---
trigger: glob
globs: routes/**/*.php
---

### Routes

-   URLs: kebab-case (`/open-source`)
-   Route names: camelCase (`->name('openSource')`)
-   Parameters: camelCase (`{userId}`)
-   Use tuple notation: `[Controller::class, 'method']`
-   write pest tests for any new route created

## API Routing

-   Use plural resource names: `/errors`
-   Use kebab-case: `/error-occurrences`
-   Limit deep nesting for simplicity:
    ```
    /error-occurrences/1
    /errors/1/occurrences
    ```
