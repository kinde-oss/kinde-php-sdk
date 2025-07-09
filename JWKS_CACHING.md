# JWKS Caching Implementation

## Overview

The Kinde PHP SDK now includes JWKS (JSON Web Key Set) caching to improve performance and reduce unnecessary network requests.

## Problem Solved

Previously, the SDK would download the JWKS file from the server every time a JWT token needed to be validated. This caused:
- Increased latency for authentication checks
- Unnecessary load on Kinde servers
- Poor performance for applications with frequent token validations

## Solution

The SDK now implements intelligent JWKS caching with the following features:

### Cache Storage
- JWKS data is cached using the existing cookie-based storage system
- Cache includes TTL (Time To Live) with default of 1 hour
- Automatic cache expiration handling

### Cache Strategy
1. **Cache-First**: Check for cached JWKS before making HTTP requests
2. **Fallback**: If cached JWKS fails to validate a token, refresh from server
3. **Automatic Refresh**: Cache is automatically updated when keys rotate

### Cache Methods

#### Storage Class Methods
```php
// Get cached JWKS data
$jwks = Storage::getInstance()->getCachedJwks();

// Set JWKS data in cache (TTL defaults to 1 hour)
Storage::getInstance()->setCachedJwks($jwks, 3600);

// Clear cached JWKS data
Storage::getInstance()->clearCachedJwks();
```

#### Client Class Methods
```php
// Clear JWKS cache via client instance
$kindeClient->clearJwksCache();
```

## Performance Benefits

- **Reduced Network Requests**: JWKS is downloaded only once per hour (configurable)
- **Faster Token Validation**: No network latency for cached JWKS
- **Better Scalability**: Reduced load on Kinde servers
- **Improved User Experience**: Faster authentication checks

## Configuration

The default cache TTL is 1 hour (3600 seconds). This can be adjusted when setting cached JWKS:

```php
// Cache for 30 minutes
Storage::getInstance()->setCachedJwks($jwks, 1800);

// Cache for 2 hours
Storage::getInstance()->setCachedJwks($jwks, 7200);
```

## Error Handling

The implementation includes robust error handling:

1. **Cache Miss**: Automatically fetches from server
2. **Invalid Cache**: Clears corrupted cache and fetches fresh data
3. **Network Failure**: Gracefully falls back to null return
4. **Key Rotation**: Automatically detects when cached keys fail and refreshes

## Testing

Run the JWKS caching tests:

```bash
vendor/bin/phpunit test/UnitTests/JwksCachingTest.php
```

## Migration

This change is **backward compatible**. Existing code will continue to work without modification, but will now benefit from improved performance due to caching.

## Monitoring

To monitor cache effectiveness, you can:
- Check cache hit rates by logging cache operations
- Monitor network requests to JWKS endpoints
- Use the `clearJwksCache()` method for testing cache behavior 