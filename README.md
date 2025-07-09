# Kinde PHP SDK

The Kinde SDK for PHP.

You can also use the PHP starter kit [here](https://github.com/kinde-starter-kits/php-starter-kit).

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](https://makeapullrequest.com) [![Kinde Docs](https://img.shields.io/badge/Kinde-Docs-eee?style=flat-square)](https://kinde.com/docs/developer-tools) [![Kinde Community](https://img.shields.io/badge/Kinde-Community-eee?style=flat-square)](https://thekindecommunity.slack.com)

## Features

### JWKS Caching
The SDK now includes intelligent JWKS (JSON Web Key Set) caching to improve performance:
- **Reduced Network Requests**: JWKS is cached for 1 hour by default
- **Automatic Fallback**: If cached keys fail, automatically refresh from server
- **Better Performance**: Faster token validation with no network latency for cached JWKS
- **Backward Compatible**: No code changes required for existing implementations

For detailed information about JWKS caching, see [JWKS_CACHING.md](JWKS_CACHING.md).

## Documentation

For details on integrating this SDK into your project, head over to the [Kinde docs](https://kinde.com/docs/) and see the [PHP SDK](https://kinde.com/docs/developer-tools/php-sdk) doc 👍🏼.

## Development

### Prerequisites

To generate the API code, you'll need:
- PHP 7.4 or higher
- Composer
- Node.js (for API generation)

### Installation

Install all dependencies:
```bash
composer install
npm install
```

This will automatically:
- Install PHP dependencies via Composer
- Install Node.js dependencies via npm

### Generating API Code

The API code is generated from the Kinde OpenAPI specification. To regenerate the API code:

```bash
composer generate-api
```

This will:
- Remove the existing API and Model directories
- Use the installed OpenAPI Generator
- Download the latest OpenAPI spec from Kinde
- Generate new API code
- Update the autoloader
- Clean up temporary files

## Publishing

The core team handles publishing.

## Contributing

Please refer to Kinde's [contributing guidelines](https://github.com/kinde-oss/.github/blob/489e2ca9c3307c2b2e098a885e22f2239116394a/CONTRIBUTING.md).

## License
If you need help connecting to Kinde, please contact us at [support@kinde.com](mailto:support@kinde.com).
By contributing to Kinde, you agree that your contributions will be licensed under its MIT License.
