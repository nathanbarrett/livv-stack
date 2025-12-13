---
trigger: glob
globs: database/migrations/*.php
---

## Migration files

-   one migration file per table unless they are super related like a pivot table or something similar
-   all new migration files should be generated from the terminal with `php artisan make:migration some_new_migration_file`
-   foreign id creation should favor the following syntax:

```
$table->foreignIdFor(User::class)->constrained();
```

-   prefer `cascade on delete` if the foreign key column is not nullable
-   inspect any columns containing strings and make sure that the string limits are reasonable for the data they will contain
-   never create an `enum` column. use a string column of a reasonable length and let Eloquent model casting cast to an enum
