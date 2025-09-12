<?php

namespace Kinde\KindeSDK\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Model\Frontend\GetEntitlementsResponseDataEntitlementsInner;

class HasBillingEntitlementsTest extends TestCase
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

    private function createMockEntitlement(string $featureKey, int $limitMax = null): GetEntitlementsResponseDataEntitlementsInner
    {
        $entitlement = $this->createMock(GetEntitlementsResponseDataEntitlementsInner::class);
        $entitlement->method('getFeatureKey')->willReturn($featureKey);
        $entitlement->method('getEntitlementLimitMax')->willReturn($limitMax);
        return $entitlement;
    }

    public function testHasBillingEntitlementsReturnsTrueWhenNoEntitlementsProvided()
    {
        $result = $this->kindeClient->hasBillingEntitlements();
        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsReturnsTrueWhenEmptyArrayProvided()
    {
        $result = $this->kindeClient->hasBillingEntitlements([]);
        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsReturnsTrueWhenUserHasAllRequiredEntitlements()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('premium'),
            $this->createMockEntitlement('api-access'),
            $this->createMockEntitlement('advanced-features')
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements(['premium']);
        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsReturnsFalseWhenUserMissingEntitlements()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('basic')
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements(['premium']);
        $this->assertFalse($result);
    }

    public function testHasBillingEntitlementsReturnsTrueWhenUserHasAllMultipleEntitlements()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('premium'),
            $this->createMockEntitlement('api-access'),
            $this->createMockEntitlement('advanced-features')
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements(['premium', 'api-access']);
        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsReturnsFalseWhenUserHasSomeButNotAllEntitlements()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('premium')
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements(['premium', 'api-access']);
        $this->assertFalse($result);
    }

    public function testHasBillingEntitlementsWithCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('premium', 100)
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements([
            [
                'entitlement' => 'premium',
                'condition' => function($entitlement) {
                    return $entitlement->getEntitlementLimitMax() > 50;
                }
            ]
        ]);
        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsWithFailingCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('premium', 10)
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements([
            [
                'entitlement' => 'premium',
                'condition' => function($entitlement) {
                    return $entitlement->getEntitlementLimitMax() > 50;
                }
            ]
        ]);
        $this->assertFalse($result);
    }

    public function testHasBillingEntitlementsWithMissingEntitlementForCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('basic', 5)
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements([
            [
                'entitlement' => 'premium',
                'condition' => function($entitlement) {
                    return true;
                }
            ]
        ]);
        $this->assertFalse($result);
    }

    public function testHasBillingEntitlementsWithEmptyEntitlementsList()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn([]);

        $result = $this->kindeClient->hasBillingEntitlements(['premium']);
        $this->assertFalse($result);
    }

    public function testHasBillingEntitlementsAlwaysUsesApi()
    {
        // This test verifies that billing entitlements always use API calls
        // since they're not available in tokens
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('getAllEntitlements')
            ->willReturn([
                $this->createMockEntitlement('premium')
            ]);

        $result = $this->kindeClient->hasBillingEntitlements(['premium']);
        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsWithMixedSimpleAndConditionChecks()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getAllEntitlements'])
            ->getMock();

        $mockEntitlements = [
            $this->createMockEntitlement('premium', 100),
            $this->createMockEntitlement('api-access', 1000)
        ];

        $this->kindeClient->method('getAllEntitlements')
            ->willReturn($mockEntitlements);

        $result = $this->kindeClient->hasBillingEntitlements([
            'premium', // Simple check
            [
                'entitlement' => 'api-access',
                'condition' => function($entitlement) {
                    return $entitlement->getEntitlementLimitMax() > 500;
                }
            ] // Custom condition
        ]);
        $this->assertTrue($result);
    }
}
