<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\MockEntitlement;
use Kinde\KindeSDK\Tests\Support\TestableKindeClientSDK;

/**
 * Comprehensive tests for hasBillingEntitlements method.
 * Mirrors js-utils hasBillingEntitlements.test.ts test coverage.
 *
 * @covers \Kinde\KindeSDK\KindeClientSDK::hasBillingEntitlements
 * @covers \Kinde\KindeSDK\KindeClientSDK::getAllEntitlements
 */
class HasBillingEntitlementsTest extends KindeTestCase
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
    // Basic Entitlement Checks
    // =========================================================================

    public function testReturnsTrueWhenNoEntitlementsProvided(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements([]));
    }

    public function testReturnsTrueWhenUserHasAllRequiredEntitlements(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
            MockEntitlement::simple('premium_features'),
            MockEntitlement::simple('advanced_analytics'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements(['pro_gym', 'premium_features']));
    }

    public function testReturnsFalseWhenUserHasSomeButNotAllRequiredEntitlements(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
        ]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_gym', 'premium_features']));
    }

    public function testReturnsFalseWhenUserHasNoRequiredEntitlements(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('basic_plan'),
        ]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_gym', 'premium_features']));
    }

    public function testReturnsTrueWhenUserHasSingleRequiredEntitlement(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements(['pro_gym']));
    }

    public function testReturnsFalseWhenEntitlementsIsEmpty(): void
    {
        $this->client->setMockEntitlements([]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_gym']));
    }

    // =========================================================================
    // No Token Scenarios (mirrors js-utils "when no token" tests)
    // =========================================================================

    public function testReturnsFalseWhenNoTokenAndNoMockData(): void
    {
        // Create a fresh client without any mock data set
        $freshClient = new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        // No mock data set - simulates no token scenario
        $result = $freshClient->hasBillingEntitlements(['pro_gym']);

        $this->assertFalse($result);
    }

    public function testReturnsTrueWhenNoTokenButEmptyEntitlementsRequired(): void
    {
        // Create a fresh client without any mock data set
        $freshClient = new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        // Empty entitlements array should return true (no entitlements required)
        $result = $freshClient->hasBillingEntitlements([]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Custom Conditions
    // =========================================================================

    public function testCustomConditionReturnsTrueWhenConditionPasses(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35,
                priceName: 'Pro gym'
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_gym',
                'condition' => fn($entitlement) => $entitlement->getFeatureKey() === 'pro_gym',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionReturnsFalseWhenConditionFails(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_gym',
                'condition' => fn() => false, // Always fails
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCustomConditionCanAccessEntitlementProperties(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35,
                priceName: 'Pro gym',
                entitlementLimitMax: 100,
                entitlementLimitMin: 1
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_gym',
                'condition' => function ($entitlement) {
                    return $entitlement->getFixedCharge() >= 30 &&
                           $entitlement->getPriceName() === 'Pro gym' &&
                           $entitlement->getEntitlementLimitMax() === 100;
                },
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCombiningStringEntitlementsAndCustomConditions(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('premium_features'),
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            'premium_features', // string entitlement
            [
                'entitlement' => 'pro_gym',
                'condition' => fn($entitlement) => $entitlement->getFeatureKey() === 'pro_gym',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testOneConditionFailsInMixedTypes(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('premium_features'),
        ]);

        $result = $this->client->hasBillingEntitlements([
            'premium_features', // passes
            [
                'entitlement' => 'pro_gym',
                'condition' => fn() => true, // Won't be called because entitlement doesn't exist
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testMultipleCustomConditionsAllPassing(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35
            ),
            new MockEntitlement(
                featureKey: 'premium_features',
                featureName: 'Premium Features',
                id: 'ent_2',
                fixedCharge: 50
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_gym',
                'condition' => fn($e) => $e->getFixedCharge() === 35,
            ],
            [
                'entitlement' => 'premium_features',
                'condition' => fn($e) => $e->getFixedCharge() === 50,
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testMultipleCustomConditionsOneFailing(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35
            ),
            new MockEntitlement(
                featureKey: 'premium_features',
                featureName: 'Premium Features',
                id: 'ent_2',
                fixedCharge: 50
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_gym',
                'condition' => fn($e) => $e->getFixedCharge() === 35, // passes
            ],
            [
                'entitlement' => 'premium_features',
                'condition' => fn($e) => $e->getFixedCharge() === 100, // fails
            ],
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Error Handling
    // =========================================================================

    public function testReturnsFalseOnException(): void
    {
        $this->client->setEntitlementsException(new Exception('API Error'));

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_gym']));
    }

    public function testGracefullyHandlesApiFailure(): void
    {
        $this->client->setEntitlementsException(new Exception('Network timeout'));

        // Should not throw, should return false
        $result = $this->client->hasBillingEntitlements(['pro_gym', 'premium_features']);
        
        $this->assertFalse($result);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testEntitlementKeysAreCaseSensitive(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('Pro_Gym'),
        ]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_gym']));
        $this->assertTrue($this->client->hasBillingEntitlements(['Pro_Gym']));
    }

    public function testLargeNumberOfEntitlements(): void
    {
        $entitlements = [];
        for ($i = 1; $i <= 100; $i++) {
            $entitlements[] = MockEntitlement::simple("entitlement_$i");
        }
        
        $this->client->setMockEntitlements($entitlements);

        $this->assertTrue($this->client->hasBillingEntitlements([
            'entitlement_1', 
            'entitlement_50', 
            'entitlement_100'
        ]));
        $this->assertFalse($this->client->hasBillingEntitlements([
            'entitlement_1', 
            'entitlement_101'
        ]));
    }

    public function testSpecialCharactersInEntitlementKeys(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('entitlement:admin'),
            MockEntitlement::simple('entitlement.editor'),
            MockEntitlement::simple('entitlement-viewer'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements([
            'entitlement:admin', 
            'entitlement.editor', 
            'entitlement-viewer'
        ]));
    }

    public function testEarlyExitOnFirstFailure(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('existing_entitlement'),
        ]);

        // Should stop checking after 'missing' fails
        $result = $this->client->hasBillingEntitlements(['missing', 'existing_entitlement']);

        $this->assertFalse($result);
    }

    public function testEntitlementWithNullProperties(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'basic_plan',
                featureName: 'Basic Plan',
                id: 'ent_1',
                fixedCharge: null,
                priceName: null,
                unitAmount: null,
                entitlementLimitMax: null,
                entitlementLimitMin: null
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'basic_plan',
                'condition' => fn($e) => $e->getFixedCharge() === null,
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testEntitlementWithZeroValues(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'free_tier',
                featureName: 'Free Tier',
                id: 'ent_1',
                fixedCharge: 0,
                entitlementLimitMax: 0,
                entitlementLimitMin: 0
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'free_tier',
                'condition' => fn($e) => $e->getFixedCharge() === 0 && 
                                         $e->getEntitlementLimitMax() === 0,
            ],
        ]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Method Call Verification
    // =========================================================================

    public function testMethodCallIsRecorded(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
        ]);

        $this->client->hasBillingEntitlements(['pro_gym']);

        $this->assertTrue($this->client->wasMethodCalled('hasBillingEntitlements'));
        $this->assertTrue($this->client->wasMethodCalled('getAllEntitlements'));
    }

    public function testMethodCallRecordsEntitlementsList(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
        ]);

        $entitlements = ['pro_gym', 'premium_features'];
        $this->client->hasBillingEntitlements($entitlements);

        $calls = $this->client->getMethodCalls('hasBillingEntitlements');
        $this->assertCount(1, $calls);
        $this->assertEquals($entitlements, $calls[0]['billingEntitlements']);
    }

    public function testMultipleCallsAreRecorded(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
            MockEntitlement::simple('premium'),
        ]);

        $this->client->hasBillingEntitlements(['pro_gym']);
        $this->client->hasBillingEntitlements(['premium']);

        $this->assertEquals(2, $this->client->getMethodCallCount('hasBillingEntitlements'));
    }
}

