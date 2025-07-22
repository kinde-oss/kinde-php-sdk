# Testing Guide for Kinde PHP SDK

This document provides comprehensive information about testing the Kinde PHP SDK, including how to run tests, what tests are available, and how to contribute to testing.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Test Structure](#test-structure)
3. [Running Tests](#running-tests)
4. [Test Suites](#test-suites)
5. [Framework-Specific Tests](#framework-specific-tests)
6. [Examples](#examples)
7. [Coverage Reports](#coverage-reports)
8. [Contributing to Tests](#contributing-to-tests)
9. [Troubleshooting](#troubleshooting)

## Quick Start

### Prerequisites

- PHP 8.0 or higher
- Composer
- PHPUnit 10.x

### Installation

```bash
# Install dependencies
composer install

# Run all tests
./run-tests.sh all

# Or run specific test suites
./run-tests.sh core
./run-tests.sh laravel
```

## Test Structure

```
tests/
├── Unit/                          # Core SDK unit tests
│   ├── KindeClientSDKTest.php
│   └── KindeManagementClientTest.php
├── Framework/                     # Framework-specific tests
│   ├── Laravel/
│   │   ├── KindeServiceProviderTest.php
│   │   ├── KindeAuthMiddlewareTest.php
│   │   └── KindeAuthControllerTest.php
│   ├── Slim/
│   ├── Symfony/
│   └── CodeIgniter/
├── Integration/                   # Integration tests
└── examples/                      # Example applications
    ├── laravel/
    ├── slim/
    ├── symfony/
    └── codeigniter/
```

## Running Tests

### Using the Test Runner Script

The `run-tests.sh` script provides an easy way to run different test suites:

```bash
# Run all tests
./run-tests.sh all

# Run specific test suites
./run-tests.sh core
./run-tests.sh laravel
./run-tests.sh slim
./run-tests.sh symfony
./run-tests.sh codeigniter
./run-tests.sh integration

# Show coverage information
./run-tests.sh coverage

# Clean up test artifacts
./run-tests.sh clean

# Show help
./run-tests.sh help
```

### Using PHPUnit Directly

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test suite
php vendor/bin/phpunit --testsuite="Core SDK"
php vendor/bin/phpunit --testsuite="Laravel Framework"

# Run specific test file
php vendor/bin/phpunit tests/Unit/KindeClientSDKTest.php

# Run with coverage
php vendor/bin/phpunit --coverage-html=coverage/html
```

## Test Suites

### Core SDK Tests (`tests/Unit/`)

Tests for the core SDK functionality:

- **KindeClientSDKTest.php**: Tests for the main OAuth client
  - Environment variable initialization
  - Constructor parameter handling
  - Endpoint generation
  - Authentication flow
  - Error handling

- **KindeManagementClientTest.php**: Tests for the management API client
  - Environment variable initialization
  - API client initialization
  - Configuration handling
  - Access token management

### Framework Tests (`tests/Framework/`)

Tests for framework-specific implementations:

#### Laravel Framework Tests

- **KindeServiceProviderTest.php**: Tests the Laravel service provider
  - Service registration
  - Configuration handling
  - Environment variable fallbacks
  - Singleton registration

- **KindeAuthMiddlewareTest.php**: Tests the authentication middleware
  - Authentication checks
  - Redirect handling
  - JSON response handling
  - Inertia.js integration

- **KindeAuthControllerTest.php**: Tests the authentication controller
  - Login/logout flows
  - Callback handling
  - Portal URL generation
  - Error handling

#### Other Framework Tests

Similar test structures exist for:
- Slim Framework
- Symfony Framework
- CodeIgniter Framework

### Integration Tests (`tests/Integration/`)

End-to-end tests that verify the SDK works correctly in real-world scenarios.

## Framework-Specific Tests

### Laravel Tests

Laravel tests require a Laravel application context. The tests mock the Laravel framework components:

```php
// Example: Testing service provider registration
public function testServiceProviderRegistersKindeClientSDK()
{
    Config::set('kinde', [
        'domain' => 'https://test-domain.kinde.com',
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret'
    ]);

    $app = $this->createApplication();
    $provider = new KindeServiceProvider($app);
    $provider->register();

    $kindeClient = $app->make(KindeClientSDK::class);
    $this->assertInstanceOf(KindeClientSDK::class, $kindeClient);
}
```

### Middleware Tests

Middleware tests verify authentication and authorization behavior:

```php
// Example: Testing middleware with authenticated user
public function testMiddlewareAllowsAuthenticatedUser()
{
    $this->kindeClient->method('isAuthenticated')->willReturn(true);
    
    $request = Request::create('/protected-route', 'GET');
    $response = $this->middleware->handle($request, $next);
    
    $this->assertEquals('Protected content', $response->getContent());
}
```

## Examples

The `examples/` directory contains complete example applications for each framework:

### Laravel Example

```php
// examples/laravel/ExampleController.php
class ExampleController extends Controller
{
    public function __construct(
        private KindeClientSDK $kindeClient,
        private KindeManagementClient $management
    ) {}

    public function login(Request $request): RedirectResponse
    {
        $additionalParams = $request->only(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            return redirect()->away($result->getAuthUrl());
        } catch (Exception $e) {
            return redirect()->route('home')->withErrors(['auth' => $e->getMessage()]);
        }
    }
}
```

### Slim Example

```php
// examples/slim/ExampleApp.php
$app->get('/auth/login', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->login($request, $response);
});

$app->get('/api/users', function (Request $request, Response $response) use ($management) {
    try {
        $users = $management->users->getUsers();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus($e->getCode());
    }
});
```

## Coverage Reports

After running tests, coverage reports are generated in the `coverage/` directory:

```bash
# Generate coverage reports
./run-tests.sh all

# View coverage
open coverage/all/index.html
```

Coverage reports include:
- HTML reports for browser viewing
- Clover XML reports for CI/CD integration
- Per-test-suite coverage breakdown

## Contributing to Tests

### Adding New Tests

1. **Unit Tests**: Add to `tests/Unit/` for core SDK functionality
2. **Framework Tests**: Add to `tests/Framework/{Framework}/` for framework-specific code
3. **Integration Tests**: Add to `tests/Integration/` for end-to-end scenarios

### Test Naming Conventions

- Test classes: `{ClassName}Test.php`
- Test methods: `test{Description}()`
- Use descriptive names that explain what is being tested

### Test Structure

```php
class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup code
    }

    public function testSomething()
    {
        // Arrange
        $input = 'test';
        
        // Act
        $result = $this->subject->method($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Mocking Guidelines

- Mock external dependencies (HTTP clients, databases)
- Use realistic test data
- Test both success and failure scenarios
- Test edge cases and error conditions

### Environment Variables

Tests use predefined environment variables in `phpunit.xml`:

```xml
<env name="KINDE_DOMAIN" value="https://test-domain.kinde.com"/>
<env name="KINDE_CLIENT_ID" value="test_client_id"/>
<env name="KINDE_CLIENT_SECRET" value="test_client_secret"/>
```

## Troubleshooting

### Common Issues

1. **Tests failing due to missing dependencies**
   ```bash
   composer install --dev
   ```

2. **Coverage reports not generating**
   ```bash
   # Ensure Xdebug is installed and enabled
   php -m | grep xdebug
   ```

3. **Framework tests failing**
   ```bash
   # Check if framework dependencies are installed
   composer require --dev laravel/framework
   ```

4. **Permission issues with test runner**
   ```bash
   chmod +x run-tests.sh
   ```

### Debugging Tests

```bash
# Run tests with verbose output
php vendor/bin/phpunit --verbose

# Run specific test with debug output
php vendor/bin/phpunit --debug tests/Unit/KindeClientSDKTest.php

# Run tests with coverage and stop on failure
php vendor/bin/phpunit --coverage-html=coverage/html --stop-on-failure
```

### Continuous Integration

For CI/CD pipelines, use the Clover XML reports:

```yaml
# Example GitHub Actions step
- name: Run tests
  run: |
    composer install --dev
    php vendor/bin/phpunit --coverage-clover=coverage.xml
    
- name: Upload coverage
  uses: codecov/codecov-action@v3
  with:
    file: coverage.xml
```

## Best Practices

1. **Test Isolation**: Each test should be independent and not rely on other tests
2. **Descriptive Names**: Use clear, descriptive test and method names
3. **Arrange-Act-Assert**: Structure tests with clear sections
4. **Mock External Dependencies**: Don't rely on external services in unit tests
5. **Test Both Success and Failure**: Cover error conditions and edge cases
6. **Keep Tests Fast**: Unit tests should run quickly
7. **Maintain Test Data**: Use realistic but minimal test data

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [Slim Framework Testing](https://www.slimframework.com/docs/v4/testing.html)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
- [CodeIgniter Testing](https://codeigniter4.github.io/userguide/testing/index.html) 