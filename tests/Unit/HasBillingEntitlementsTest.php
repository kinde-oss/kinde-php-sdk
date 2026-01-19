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
            MockEntitlement::simple('pro_plan'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements([]));
    }

    public function testReturnsTrueWhenUserHasAllRequiredEntitlements(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_plan'),
            MockEntitlement::simple('premium_features'),
            MockEntitlement::simple('advanced_analytics'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements(['pro_plan', 'premium_features']));
    }

    public function testReturnsFalseWhenUserHasSomeButNotAllRequiredEntitlements(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_plan'),
        ]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_plan', 'premium_features']));
    }

    public function testReturnsFalseWhenUserHasNoRequiredEntitlements(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('basic_plan'),
        ]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_plan', 'premium_features']));
    }

    public function testReturnsTrueWhenUserHasSingleRequiredEntitlement(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_plan'),
        ]);

        $this->assertTrue($this->client->hasBillingEntitlements(['pro_plan']));
    }

    public function testReturnsFalseWhenEntitlementsIsEmpty(): void
    {
        $this->client->setMockEntitlements([]);

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_plan']));
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
        $result = $freshClient->hasBillingEntitlements(['pro_plan']);

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
                'pro_plan',
                'Pro Plan',
                'ent_1',
                35,
                'Pro gym'
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_plan',
                'condition' => fn($entitlement) => $entitlement->getFeatureKey() === 'pro_plan',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionReturnsFalseWhenConditionFails(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                'pro_plan',
                'Pro Plan',
                'ent_1',
                35
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_plan',
                'condition' => fn() => false, // Always fails
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCustomConditionCanAccessEntitlementProperties(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                'pro_plan',
                'Pro Plan',
                'ent_1',
                35,
                'Pro gym',
                null,
                100,
                1
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_plan',
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
                'pro_plan',
                'Pro Plan',
                'ent_1',
                35
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            'premium_features', // string entitlement
            [
                'entitlement' => 'pro_plan',
                'condition' => fn($entitlement) => $entitlement->getFeatureKey() === 'pro_plan',
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
                'entitlement' => 'pro_plan',
                'condition' => fn() => true, // Won't be called because entitlement doesn't exist
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testMultipleCustomConditionsAllPassing(): void
    {
        $this->client->setMockEntitlements([
            new MockEntitlement(
                'pro_plan',
                'Pro Plan',
                'ent_1',
                35
            ),
            new MockEntitlement(
                'premium_features',
                'Premium Features',
                'ent_2',
                50
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_plan',
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
                'pro_plan',
                'Pro Plan',
                'ent_1',
                35
            ),
            new MockEntitlement(
                'premium_features',
                'Premium Features',
                'ent_2',
                50
            ),
        ]);

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro_plan',
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

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_plan']));
    }

    public function testGracefullyHandlesApiFailure(): void
    {
        $this->client->setEntitlementsException(new Exception('Network timeout'));

        // Should not throw, should return false
        $result = $this->client->hasBillingEntitlements(['pro_plan', 'premium_features']);
        
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

        $this->assertFalse($this->client->hasBillingEntitlements(['pro_plan']));
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
                'basic_plan',
                'Basic Plan',
                'ent_1',
                null,
                null,
                null,
                null,
                null
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
                'free_tier',
                'Free Tier',
                'ent_1',
                0,
                null,
                null,
                0,
                0
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
            MockEntitlement::simple('pro_plan'),
        ]);

        $this->client->hasBillingEntitlements(['pro_plan']);

        $this->assertTrue($this->client->wasMethodCalled('hasBillingEntitlements'));
        $this->assertTrue($this->client->wasMethodCalled('getAllEntitlements'));
    }

    public function testMethodCallRecordsEntitlementsList(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_plan'),
        ]);

        $entitlements = ['pro_plan', 'premium_features'];
        $this->client->hasBillingEntitlements($entitlements);

        $calls = $this->client->getMethodCalls('hasBillingEntitlements');
        $this->assertCount(1, $calls);
        $this->assertEquals($entitlements, $calls[0]['billingEntitlements']);
    }

    public function testMultipleCallsAreRecorded(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_plan'),
            MockEntitlement::simple('premium'),
        ]);

        $this->client->hasBillingEntitlements(['pro_plan']);
        $this->client->hasBillingEntitlements(['premium']);

        $this->assertEquals(2, $this->client->getMethodCallCount('hasBillingEntitlements'));
    }
}

