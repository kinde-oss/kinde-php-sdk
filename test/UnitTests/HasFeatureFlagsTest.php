<?php

namespace Kinde\KindeSDK\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;

class HasFeatureFlagsTest extends TestCase
{
    private $kindeClient;

    protected function setUp(): void
    {
        $this->kindeClient = new KindeClientSDK(
            domain: 'https://test.kinde.com',
            redirectUri: 'http://localhost:3000/callback',
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            grantType: 'authorization_code'
        );
    }

    public function testHasFeatureFlagsReturnsTrueWhenNoFlagsProvided()
    {
        $result = $this->kindeClient->hasFeatureFlags();
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsReturnsTrueWhenEmptyArrayProvided()
    {
        $result = $this->kindeClient->hasFeatureFlags([]);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsReturnsTrueWhenUserHasAllRequiredFlags()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => true, 't' => 'b'],
                    'theme' => ['v' => 'dark', 't' => 's'],
                    'maxUsers' => ['v' => 100, 't' => 'i']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags(['darkMode']);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsReturnsFalseWhenUserMissingFlags()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'theme' => ['v' => 'light', 't' => 's']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags(['darkMode']);
        $this->assertFalse($result);
    }

    public function testHasFeatureFlagsReturnsTrueWhenUserHasAllMultipleFlags()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => true, 't' => 'b'],
                    'theme' => ['v' => 'dark', 't' => 's'],
                    'maxUsers' => ['v' => 100, 't' => 'i']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags(['darkMode', 'theme']);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsReturnsFalseWhenUserHasSomeButNotAllFlags()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => true, 't' => 'b']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags(['darkMode', 'theme']);
        $this->assertFalse($result);
    }

    public function testHasFeatureFlagsWithSpecificBooleanValue()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => true, 't' => 'b']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags([
            ['flag' => 'darkMode', 'value' => true]
        ]);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsWithSpecificStringValue()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'theme' => ['v' => 'dark', 't' => 's']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags([
            ['flag' => 'theme', 'value' => 'dark']
        ]);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsWithSpecificIntegerValue()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'maxUsers' => ['v' => 100, 't' => 'i']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags([
            ['flag' => 'maxUsers', 'value' => 100]
        ]);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsReturnsFalseForWrongValue()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => false, 't' => 'b']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags([
            ['flag' => 'darkMode', 'value' => true]
        ]);
        $this->assertFalse($result);
    }

    public function testHasFeatureFlagsReturnsTrueWhenFlagExistsButValueIsFalse()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => false, 't' => 'b']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags(['darkMode']);
        $this->assertTrue($result); // Flag exists, value doesn't matter for simple check
    }

    public function testHasFeatureFlagsUsesForceApiParameter()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getFeatureFlagsFromApi'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('getFeatureFlagsFromApi')
            ->willReturn([
                'darkMode' => ['v' => true, 't' => 'b']
            ]);

        $result = $this->kindeClient->hasFeatureFlags(['darkMode'], true);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsWithMixedSimpleAndSpecificChecks()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'darkMode' => ['v' => true, 't' => 'b'],
                    'theme' => ['v' => 'dark', 't' => 's']
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags([
            'darkMode', // Simple check
            ['flag' => 'theme', 'value' => 'dark'] // Specific value check
        ]);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsWithNonArrayFlagData()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn([
                'value' => [
                    'simpleFlag' => true // Direct value, not array with 'v' and 't'
                ]
            ]);

        $result = $this->kindeClient->hasFeatureFlags([
            ['flag' => 'simpleFlag', 'value' => true]
        ]);
        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsGracefullyHandlesMissingClaim()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaim'])
            ->getMock();

        $this->kindeClient->method('getClaim')
            ->with('feature_flags')
            ->willReturn(['value' => null]);

        $this->assertFalse($this->kindeClient->hasFeatureFlags(['any']));
    }
}
