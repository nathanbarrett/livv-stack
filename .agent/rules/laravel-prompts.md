---
trigger: glob
globs: app/AI/Prompts/*.php
---

### AI Prompts

- All AI prompt classes should be suffixed with "Prompt", e.g., `GenerateBlogPostDescriptionPrompt`.
- All prompt classes should use the Laravel Prism library to interact with AI models.
- When using a proivder and model always use the `app/AI/enums/ProviderModel.php` for both the model and provider like so:
  ```php
  use App\AI\Enums\ProviderModel;
  
  $model = ProviderModel::OPENAI_TEXT_GPT_5_2;
  
  Prism::text()
      ->using($model::provider(), $model->value)
      ...
  ```
- If you are wanting structured output from the AI always use `Prism::structured()...->asStructured()`
- Every prompt class should have a public method named `handle` that accepts the necessary parameters for generating the prompt.
- The return from `handle` should NEVER be a class from Prism. It should be a primitive type like `string`, `array`, `int`, `float`, or `bool`.
