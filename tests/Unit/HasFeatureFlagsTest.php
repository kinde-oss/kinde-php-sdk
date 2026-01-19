<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\TestableKindeClientSDK;

/**
 * Comprehensive tests for hasFeatureFlags method.
 * Mirrors js-utils hasFeatureFlags.test.ts test coverage.
 */
class HasFeatureFlagsTest extends KindeTestCase
{
    private TestableKindeClientSDK $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );
    }

    // =========================================================================
    // Basic Feature Flag Checks
    // =========================================================================

    public function testReturnsTrueWhenNoFeatureFlagsProvided(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags([]));
    }

    public function testReturnsTrueWhenUserHasAllRequiredFeatureFlags(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
                'newDashboard' => ['v' => 'enabled', 't' => 's'],
                'maxUsers' => ['v' => 100, 't' => 'i'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['darkMode', 'newDashboard']));
    }

    public function testReturnsFalseWhenUserHasSomeButNotAllRequiredFeatureFlags(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $this->assertFalse($this->client->hasFeatureFlags(['darkMode', 'newDashboard']));
    }

    public function testReturnsFalseWhenUserHasNoRequiredFeatureFlags(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'otherFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $this->assertFalse($this->client->hasFeatureFlags(['darkMode', 'newDashboard']));
    }

    public function testReturnsTrueWhenUserHasSingleRequiredFeatureFlag(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['darkMode']));
    }

    public function testReturnsFalseWhenFeatureFlagsIsNull(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => null,
        ]);

        $this->assertFalse($this->client->hasFeatureFlags(['darkMode']));
    }

    public function testReturnsFalseWhenFeatureFlagsIsEmpty(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [],
        ]);

        $this->assertFalse($this->client->hasFeatureFlags(['darkMode']));
    }

    // =========================================================================
    // Feature Flag Exists with Different Value Types
    // =========================================================================

    public function testFeatureFlagExistsWithFalseBooleanValue(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => false, 't' => 'b'],
            ],
        ]);

        // Flag exists, so should return true (checking existence, not value)
        $this->assertTrue($this->client->hasFeatureFlags(['darkMode']));
    }

    public function testMixingDifferentFeatureFlagTypes(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
                'theme' => ['v' => 'blue', 't' => 's'],
                'maxItems' => ['v' => 50, 't' => 'i'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['darkMode', 'theme', 'maxItems']));
    }

    // =========================================================================
    // Feature Flag KV Conditions (Value Checking)
    // =========================================================================

    public function testFlagExistsAndValueMatchesExactly(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'maxUsers' => ['v' => 100, 't' => 'i'],
                'isEnabled' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags([
            ['flag' => 'theme', 'value' => 'dark'],
            ['flag' => 'maxUsers', 'value' => 100],
            ['flag' => 'isEnabled', 'value' => true],
        ]);

        $this->assertTrue($result);
    }

    public function testFlagExistsButValueDoesNotMatch(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'maxUsers' => ['v' => 100, 't' => 'i'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags([
            ['flag' => 'theme', 'value' => 'light'], // mismatch
            ['flag' => 'maxUsers', 'value' => 100],
        ]);

        $this->assertFalse($result);
    }

    public function testFlagDoesNotExist(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags([
            ['flag' => 'nonExistentFlag', 'value' => 'any'],
        ]);

        $this->assertFalse($result);
    }

    public function testMixingStringFlagsAndKvConditions(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
                'theme' => ['v' => 'blue', 't' => 's'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags([
            'darkMode', // string flag - just check existence
            ['flag' => 'theme', 'value' => 'blue'], // KV condition - check existence and value
        ]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Mixed Flag Types
    // =========================================================================

    public function testCombiningBothFlagTypes(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'basicFlag' => ['v' => false, 't' => 'b'],
                'theme' => ['v' => 'dark', 't' => 's'],
                'customFlag' => ['v' => 'enabled', 't' => 's'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags([
            'basicFlag', // string flag
            ['flag' => 'theme', 'value' => 'dark'], // KV condition
        ]);

        $this->assertTrue($result);
    }

    public function testOneConditionFailsInMixedTypes(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'basicFlag' => ['v' => true, 't' => 'b'],
                'theme' => ['v' => 'dark', 't' => 's'],
                'customFlag' => ['v' => 'enabled', 't' => 's'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags([
            'basicFlag', // string flag - passes
            ['flag' => 'theme', 'value' => 'light'], // KV condition - fails (value mismatch)
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Value Type Matching
    // =========================================================================

    public function testBooleanValueMatching(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'isEnabled' => ['v' => true, 't' => 'b'],
                'isDisabled' => ['v' => false, 't' => 'b'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags([
            ['flag' => 'isEnabled', 'value' => true],
        ]));

        $this->assertTrue($this->client->hasFeatureFlags([
            ['flag' => 'isDisabled', 'value' => false],
        ]));

        $this->assertFalse($this->client->hasFeatureFlags([
            ['flag' => 'isEnabled', 'value' => false],
        ]));
    }

    public function testStringValueMatching(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'mode' => ['v' => 'production', 't' => 's'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags([
            ['flag' => 'theme', 'value' => 'dark'],
        ]));

        $this->assertFalse($this->client->hasFeatureFlags([
            ['flag' => 'theme', 'value' => 'light'],
        ]));
    }

    public function testIntegerValueMatching(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'maxUsers' => ['v' => 100, 't' => 'i'],
                'minItems' => ['v' => 0, 't' => 'i'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags([
            ['flag' => 'maxUsers', 'value' => 100],
        ]));

        $this->assertTrue($this->client->hasFeatureFlags([
            ['flag' => 'minItems', 'value' => 0],
        ]));

        $this->assertFalse($this->client->hasFeatureFlags([
            ['flag' => 'maxUsers', 'value' => 50],
        ]));
    }

    public function testStrictTypeComparisonForValues(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'count' => ['v' => 100, 't' => 'i'],
                'enabled' => ['v' => true, 't' => 'b'],
            ],
        ]);

        // Integer 100 should not equal string "100"
        $this->assertFalse($this->client->hasFeatureFlags([
            ['flag' => 'count', 'value' => '100'],
        ]));

        // Boolean true should not equal integer 1
        $this->assertFalse($this->client->hasFeatureFlags([
            ['flag' => 'enabled', 'value' => 1],
        ]));
    }

    // =========================================================================
    // Error Handling
    // =========================================================================

    public function testReturnsFalseOnException(): void
    {
        $this->client->setFeatureFlagsException(new Exception('API Error'));

        $this->assertFalse($this->client->hasFeatureFlags(['darkMode']));
    }

    public function testGracefullyHandlesApiFailure(): void
    {
        $this->client->setFeatureFlagsException(new Exception('Network timeout'));

        // Should not throw, should return false
        $result = $this->client->hasFeatureFlags(['darkMode', 'newDashboard']);
        
        $this->assertFalse($result);
    }

    // =========================================================================
    // ForceApi Option
    // =========================================================================

    public function testForceApiFalseUsesTokenData(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags(['darkMode'], false);

        $this->assertTrue($result);
    }

    public function testForceApiNullUsesTokenData(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->hasFeatureFlags(['darkMode']);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testFlagKeysAreCaseSensitive(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'DarkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $this->assertFalse($this->client->hasFeatureFlags(['darkMode']));
        $this->assertTrue($this->client->hasFeatureFlags(['DarkMode']));
    }

    public function testLargeNumberOfFeatureFlags(): void
    {
        $flags = [];
        for ($i = 1; $i <= 100; $i++) {
            $flags["flag_$i"] = ['v' => true, 't' => 'b'];
        }
        
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => $flags,
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['flag_1', 'flag_50', 'flag_100']));
        $this->assertFalse($this->client->hasFeatureFlags(['flag_1', 'flag_101']));
    }

    public function testSpecialCharactersInFlagKeys(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'flag:admin' => ['v' => true, 't' => 'b'],
                'flag.editor' => ['v' => 'enabled', 't' => 's'],
                'flag-viewer' => ['v' => 100, 't' => 'i'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['flag:admin', 'flag.editor', 'flag-viewer']));
    }

    public function testEmptyStringFlagKey(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                '' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['']));
    }

    public function testNullFlagValue(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'nullFlag' => ['v' => null, 't' => 's'],
            ],
        ]);

        $this->assertTrue($this->client->hasFeatureFlags(['nullFlag']));
        
        // Check null value matches
        $this->assertTrue($this->client->hasFeatureFlags([
            ['flag' => 'nullFlag', 'value' => null],
        ]));
    }

    public function testEarlyExitOnFirstFailure(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'existingFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);

        // Should stop checking after 'missing' fails
        $result = $this->client->hasFeatureFlags(['missing', 'existingFlag']);

        $this->assertFalse($result);
    }
}

