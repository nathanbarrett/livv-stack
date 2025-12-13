---
trigger: glob
globs: app/Console/Commands/*.php
---

### Artisan Commands

-   Names: kebab-case (`delete-old-records`)
-   always prefer prompts from the new prompt library i.e. `use function Laravel\Prompts\text;`
-   Always provide feedback (`$this->comment('All ok!')`)
-   Show progress for loops, summary at end
-   Put output BEFORE processing item (easier debugging):

    ```php
    $items->each(function(Item $item) {
        $this->info("Processing item id `{$item->id}`...");
        $this->processItem($item);
    });

    $this->comment("Processed {$items->count()} items.");
    ```
