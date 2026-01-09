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
-   Endpoints that return views, redirects, or Inertia pages should go in the web.php routes. Routes that deal with data (CRUD, etc) should go in the api.php routes file. It's important to note that api.php is loaded FROM the web.php file which means that it has access to web middleware like 'auth'.
-   Controllers for the api.php routes should go in `App\Http\Controllers\Api` and be prefixed with `Api`, e.g. `ApiPostController`.
