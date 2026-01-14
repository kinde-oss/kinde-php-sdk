<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    private const JWKS_DATA_URL = 'data://text/plain,{"keys":[]}';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::setJwksUrl(self::JWKS_DATA_URL);
        Storage::clear();
    }

    public function testSetAndGetCachedJwks(): void
    {
        $jwks = ['keys' => [['kid' => 'test-key']]];

        Storage::setCachedJwks($jwks, 3600);

        $this->assertSame($jwks, Storage::getCachedJwks());
    }

    public function testParseJwtClearsCachedJwksOnFailure(): void
    {
        Storage::setCachedJwks(['keys' => [['kid' => 'bad-key']]], 3600);
        $_COOKIE['kinde_' . StorageEnums::JWKS_CACHE] = json_encode([
            'jwks' => ['keys' => [['kid' => 'bad-key']]],
            'expires_at' => time() + 3600,
        ]);

        $result = Utils::parseJWT('invalid.token.value');

        $this->assertNull($result);
        $this->assertNull(Storage::getCachedJwks());
    }

    public function testIsSessionPersistentDefaultsToTrueWithoutToken(): void
    {
        Storage::clear();
        $this->assertTrue(Storage::isSessionPersistent());
    }

    public function testGetCookieExpirationReturnsPersistentTimestamp(): void
    {
        Storage::clear();
        $expiration = Storage::getCookieExpiration();

        $this->assertGreaterThan(time(), $expiration);
    }
}

