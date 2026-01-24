# Code style & architecture

This guidelines apply in addition to those contained in the boost guidelines.

## Style guidelines
- When calling PHP methods, always use named parameters. For long calls, break each parameter onto a new line.
- Do not add actions to models. Where abstracting model action logic from a controller, create an Action in the Actions namespace instead.
- Use Laravel Facades over helper methods (e.g. for redirects, auth, etc.)
- When checking booleans in conditions, be explicit (e.g. `$bool === true` instead of `$bool` snf `$bool === false` instead of `! $bool`)

## Architecture
- Do not use database enums. Use integer or string columns, with PHP enums.
- When creating model and factory, use the factory option on make:model so it includes the right typehints.