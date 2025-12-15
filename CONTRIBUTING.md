# Contributing

We're not actively seeking contributors, but if you've found a gap in functionality or have an improvement you'd like to propose, we'd love to have a look.

## Standards

### Code Coverage

**This repository maintains 100% code coverage as a strict standard.** All pull requests must:

- Include tests for any new code
- Maintain complete coverage for all modified code
- Pass the automated coverage gate

Run coverage locally before submitting:

```bash
composer test-coverage
```

### Code Style

We use Laravel Pint for code formatting:

```bash
composer pint
```

### Static Analysis

We use PHPStan for static analysis:

```bash
composer analyse
```

## Submitting Changes

1. Fork the repository
2. Create a feature branch
3. Write tests first (TDD encouraged)
4. Implement your changes
5. Ensure 100% coverage is maintained
6. Run all quality checks:
   ```bash
   composer pint
   composer analyse
   composer test-coverage
   ```
7. Submit a pull request with a clear description of the change

## Questions

Open an issue for discussion before starting significant work.
