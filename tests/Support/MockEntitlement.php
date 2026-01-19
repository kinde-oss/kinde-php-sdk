<?php

namespace Kinde\KindeSDK\Tests\Support;

/**
 * Mock entitlement object for testing.
 * Mimics the structure of GetEntitlementsResponseDataEntitlementsInner.
 */
class MockEntitlement
{
    private string $id;
    private string $featureKey;
    private string $featureName;
    private ?int $fixedCharge;
    private ?string $priceName;
    private ?int $unitAmount;
    private ?int $entitlementLimitMax;
    private ?int $entitlementLimitMin;

    public function __construct(
        string $featureKey,
        string $featureName = '',
        string $id = '',
        ?int $fixedCharge = null,
        ?string $priceName = null,
        ?int $unitAmount = null,
        ?int $entitlementLimitMax = null,
        ?int $entitlementLimitMin = null
    ) {
        $this->id = $id ?: 'entitlement_' . bin2hex(random_bytes(4));
        $this->featureKey = $featureKey;
        $this->featureName = $featureName ?: ucfirst(str_replace('_', ' ', $featureKey));
        $this->fixedCharge = $fixedCharge;
        $this->priceName = $priceName;
        $this->unitAmount = $unitAmount;
        $this->entitlementLimitMax = $entitlementLimitMax;
        $this->entitlementLimitMin = $entitlementLimitMin;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFeatureKey(): string
    {
        return $this->featureKey;
    }

    public function getFeatureName(): string
    {
        return $this->featureName;
    }

    public function getFixedCharge(): ?int
    {
        return $this->fixedCharge;
    }

    public function getPriceName(): ?string
    {
        return $this->priceName;
    }

    public function getUnitAmount(): ?int
    {
        return $this->unitAmount;
    }

    public function getEntitlementLimitMax(): ?int
    {
        return $this->entitlementLimitMax;
    }

    public function getEntitlementLimitMin(): ?int
    {
        return $this->entitlementLimitMin;
    }

    /**
     * Create a mock entitlement from an array configuration.
     *
     * @param array $config Configuration array
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['feature_key'] ?? $config['featureKey'] ?? '',
            $config['feature_name'] ?? $config['featureName'] ?? '',
            $config['id'] ?? '',
            $config['fixed_charge'] ?? $config['fixedCharge'] ?? null,
            $config['price_name'] ?? $config['priceName'] ?? null,
            $config['unit_amount'] ?? $config['unitAmount'] ?? null,
            $config['entitlement_limit_max'] ?? $config['entitlementLimitMax'] ?? null,
            $config['entitlement_limit_min'] ?? $config['entitlementLimitMin'] ?? null
        );
    }

    /**
     * Create multiple mock entitlements from array configurations.
     *
     * @param array $configs Array of configuration arrays
     * @return array Array of MockEntitlement objects
     */
    public static function createMany(array $configs): array
    {
        return array_map([self::class, 'fromArray'], $configs);
    }

    /**
     * Create a simple mock entitlement with just a feature key.
     *
     * @param string $featureKey The feature key
     * @return self
     */
    public static function simple(string $featureKey): self
    {
        return new self($featureKey);
    }
}

