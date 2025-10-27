# Integration Tests

## Current Status

Integration tests have been created and are fully functional. They use a complete Symfony framework stack (FrameworkBundle, translator, etc.) to test the bundle in a real-world scenario.

## Structure

```
tests/Integration/
├── App/
│   └── TestKernel.php          # Test Symfony kernel
├── IntegrationTestCase.php      # Base class for integration tests
├── DependencyInjection/
│   └── BundleIntegrationTest.php
├── FullFlowIntegrationTest.php
├── LiipImagineIntegrationTest.php
└── README.md                    # This documentation
```

## Test Approach

### Full Stack Integration Tests
The tests use a complete Symfony kernel with all necessary dependencies:
- Symfony Framework Bundle
- Liip Imagine Bundle
- Required Symfony components (translation, string, etc.)

This approach ensures:
- ✅ Real-world usage scenarios
- ✅ Complete DI container compilation
- ✅ Actual Symfony kernel boot process
- ✅ Integration with Liip Imagine Bundle

## How to Run

**All tests**:
```bash
make phpunit
# or
composer test
```

**Unit tests only**:
```bash
make phpunit-unit
# or
composer test-unit
```

**Integration tests only**:
```bash
make phpunit-integration
# or
composer test-integration
```

## What Works

✅ Unit tests for all components
✅ Integration tests with full Symfony stack
✅ PHPStan static analysis
✅ ECS code style
✅ Deptrac architecture
✅ Rector quality checks
✅ Infection mutation testing

## Test Coverage

The integration tests cover:
- Bundle registration and service configuration
- URL builder with signed URLs
- Filter converter with Liip Imagine integration
- Cache resolver as drop-in replacement
- Multiple filter sets and configurations
- Special characters in file paths
- Quality settings per filter
