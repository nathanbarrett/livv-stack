---
trigger: glob
globs: app/Http/Controllers/**/*.php
---

### Controllers

-   when creating api endpoints always put validation into a custom form request
-   Plural resource names (`PostsController`)
-   Stick to CRUD methods (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`)
-   Extract new controllers for non-CRUD actions
-   for endpoints returning data favor JsonResponse responses like `return response()->json([...]);
