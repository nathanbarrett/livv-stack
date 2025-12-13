---
trigger: glob
globs: app/Models/**/*.php
---

## Eloquent Models

-   always add `protected $guarded = ['id'];` as a param
-   always create every method relationship to other models that the database schema allows for
-   `casts` are now defined in a protected method. returns an array
-   favor casting to a collection for `json` columns
-   if a `json` column carries an array of enums cast to `AsEnumCollection::of(SomeEnum::class)`
-   run `sail composer ts-helpers` at the end of any task where you add or update an Eloquent model
