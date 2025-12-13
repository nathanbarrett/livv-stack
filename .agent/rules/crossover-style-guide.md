---
trigger: model_decision
description: when working with the database or creating or updating migration files OR updating or creating Eloquent models
---

1. you can view database info with the command `sail artisan db:show` , you can see individual table info with the command `sail artisan db:table {tableName}`. you can run direct queries with `sail artisan db`.
2. keep all model factories up to date with the model schema
3. when creating new database tables you must also:
    - create a related Eloquent model with all proper castings and relationships defined
    - create a factory for the eloquent model that matches the table schema
    - NOTE: you can create all boilerplate quickly with the command `sail artisan make:model SomeModel -m -f`
