---
trigger: glob
globs: app/Http/Requests/**/*.php
---

## Custom Form Requests

-   use array style validation: `'name' => ['required', 'string', 'max:255'],`
-   where possible enforce string maximums to what their column type can hold
-   enum validation should use the Rule facade: `'type' => ['required', Rule::enum(SomeType::class)],`
