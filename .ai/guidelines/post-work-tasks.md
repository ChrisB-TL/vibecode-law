# Post work tasks

You should carry out these tasks after making changes, in addition to instructions contained in the boost guidelines.

## Ide-Helper

We use the barryvdh/laravel-ide-helper package to generate ide-helper files which we commit to git.

You should use the following commands when finished with a task: `php artisan ide-helper:generate` `php artisan ide-helper:models --nowrite --write-mixin`

## Static analysis

We use larastan/larastan for static analysis. Run it after making changes with `composer types`.

## Backend resources & typescript definitions

When your task has involved editing laravel-data resources, you should run `php artisan typescript:transform` when finished to generate typescript definitions.

When working in the frontend, you should use the types generated in `resources/js/types/generated.d.ts` rather than creating your own. If they aren't up-to-date, run `resources/js/types/generated.d.ts`.

## Wayfinder

When Laravel Wayfinder to generate typescript definitions, run `php artisan wayfinder:generate --with-form`.

## Frontend formatting and linting

Run the following when cleaning up frontend changes:
- `npm run format`
- `npm run lint`
- `npm run types`
