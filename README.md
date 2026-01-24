# vibecode.law

The open-source platform powering [vibecode.law](https://vibecode.law) — a community-driven showcase for legaltech projects — especially prototypes built with AI coding tools.

## Background

vibecode.law is an open platform for the legal community to discover and share legaltech projects. Users can submit their projects for moderation, and once approved, projects are displayed and ranked by community upvotes.

This repository contains the full source code for the platform, enabling contributors to help improve the platform.

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | [Laravel 12](https://laravel.com) (PHP 8.4) |
| Frontend | [React 19](https://react.dev) with [TypeScript](https://www.typescriptlang.org) |
| Backend-Frontend Communication | [Inertia.js v2](https://inertiajs.com) |
| Styling | [Tailwind CSS v4](https://tailwindcss.com) |

## Getting Started

If you're new to Laravel development, we recommend using [Laravel Herd](https://herd.laravel.com) — a native development environment for macOS and Windows that includes all dependencies out of the box.

**[View the full Laravel Herd Setup Guide](.github/HERD_SETUP.md)** for step-by-step installation instructions.

### AI-Assisted Development

This project uses [Laravel Boost](https://github.com/laravel/boost) to enhance AI-assisted development. Boost is an MCP server that provides AI tools with deep Laravel knowledge.

Boost automatically configures application-specific guidance that makes AI tools more effective when working on this codebase. This includes project conventions, package versions, architectural patterns and instructions on how to run its test suite and linting tools.

Boost supports:
- VS Code
- Claude Code
- Cursor
- Codex
- Gemini

To get started with Boost, from your project directory run the following command from our editor of choice and follow the terminal instructions:

```bash
php artisan boost:install
```

For troubleshooting, see the [Laravel Boost Github Readme](https://github.com/laravel/boost).

## Code Quality & Testing

```bash
php artisan format      # Format backend code
php artisan types       # Static analysis & type checks
php artisan test        # Run full test suite

php artisan lint-test   # Run code quality checks and tests
php artisan format-test # Run formatter, static analysis and then tests

npm run format          # Auto format frontend code
npm run lint            # Check for frontend code quality issues
npm run types           # Check for frontend type issues

npm run check-all       # Check formatting, linting and types.
```

## Contributing

We welcome contributions from the community. 

Please see our [Contribution Guidelines](.github/CONTRIBUTING.md) on how to get involved.

## Authors

Created by **Chris Bridges**, **Matt Pollins** and **Alex Baker**, with contributions from the Open Source Community.

## License

This project is open-sourced software licensed under the [MIT License](LICENSE).
