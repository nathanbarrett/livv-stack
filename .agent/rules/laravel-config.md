---
trigger: glob
globs: config/*.php
---

### Configuration

-   Files: kebab-case (`pdf-generator.php`)
-   Keys: snake_case (`chrome_path`)
-   Add service configs to `config/services.php`, don't create new files
-   use `env()` helper to get any .env values you need. sensible defaults
