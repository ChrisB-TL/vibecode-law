# Contributing to vibecode.law

Thank you for your interest in contributing to vibecode.law! This document provides guidelines and instructions for contributing to the project.

Contributions are welcome and will be fully credited.

Please read and understand these guidelines before creating an issue or pull request.

## Ways to Contribute

- **Report bugs** — Found something broken? Open an issue with steps to reproduce.
- **Suggest features** — Have an idea? Open an issue.
- **Fix issues or contribute features** — Submit a pull request.
- **Review pull requests** — Provide feedback on open pull requests.

## Making a PR

### Setup

1. Fork the repository via the Github UI (top right), or sync it if you've already forked it.
2. Clone your fork locally or `git pull` if you've already forked it.
3. Create a new branch for your changes

```bash
git checkout -b feat/your-feature-name  # Feature
git checkout -b fix/your-fix-name       # Fix

```

### Testing

Please ensure:
- You update tests and/or create new tests to cover both "happy" and "unhappy" paths.
- All tests are passing before submitting your PR.

```bash
php artisan test 
```

See the [README](../README.md) for more information on running tests.

### Code Quality

Please ensure you run the following commands before submitting your PR, and fix any flagged issues:

```bash
php artisan format      # Format backend code
php artisan types       # Static analysis & type checks
php artisan test        # Run full test suite

npm run format          # Auto format frontend code
npm run lint            # Check for frontend code quality issues
npm run types           # Check for frontend type issues
```

See the [README](../README.md) for more information on running quality checks.

### General tips
- Use description names for your branch and pull request.
- One pull request per feature - If you want to do more than one thing, send multiple pull requests.
- Send coherent history - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.