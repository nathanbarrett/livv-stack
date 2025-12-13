---
trigger: glob
globs: app/Policies/*.php
---

## Authorization

-   Policies use camelCase: `Gate::define('editPost', ...)`
-   Use CRUD words, but `view` instead of `show`
