<?php

namespace Kinde\KindeSDK\Tests\Unit;

use InvalidArgumentException;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;

/**
 * Unit tests for Utils class.
 * Tests utility functions for URL validation, parameter checking, and encoding.
 *
 * @covers \Kinde\KindeSDK\Sdk\Utils\Utils
 */
class UtilsTest extends KindeTestCase
{
    // =========================================================================
    // Additional Parameters Validation Tests
    // =========================================================================

    public function testCheckAdditionalParametersAcceptsValidInputs(): void
    {
        $params = [
            'audience' => 'https://test-domain.kinde.com/api',
            'org_code' => 'org_123',
            'prompt' => 'login',
            'redirect_uri' => 'https://example.com/callback',
        ];

        $result = Utils::checkAdditionalParameters($params);

        $this->assertSame($params, $result);
    }

    public function testCheckAdditionalParametersRejectsUnknownKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide correct additional, unknown_key');

        Utils::checkAdditionalParameters(['unknown_key' => 'value']);
    }

    public function testCheckAdditionalParametersRejectsWrongTypeForAudience(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid audience. Expected: string');

        Utils::checkAdditionalParameters(['audience' => 123]);
    }

    public function testCheckAdditionalParametersRejectsWrongTypeForOrgCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid org_code. Expected: string');

        Utils::checkAdditionalParameters(['org_code' => 123]);
    }

    public function testCheckAdditionalParametersReturnsEmptyArrayForEmptyInput(): void
    {
        $result = Utils::checkAdditionalParameters([]);

        $this->assertEquals([], $result);
    }

    public function testAddAdditionalParametersMergesValidatedValues(): void
    {
        $target = ['client_id' => 'test_client_id'];
        $additional = ['org_name' => 'Test Org'];

        $result = Utils::addAdditionalParameters($target, $additional);

        $this->assertSame(
            ['client_id' => 'test_client_id', 'org_name' => 'Test Org'],
            $result
        );
    }

    public function testAddAdditionalParametersPreservesTargetOnEmptyAdditional(): void
    {
        $target = ['client_id' => 'test_client_id', 'scope' => 'openid'];
        
        $result = Utils::addAdditionalParameters($target, []);

        $this->assertSame($target, $result);
    }

    public function testAddAdditionalParametersOverwritesTargetValues(): void
    {
        $target = ['client_id' => 'original', 'org_code' => 'org_1'];
        $additional = ['org_code' => 'org_2'];

        $result = Utils::addAdditionalParameters($target, $additional);

        $this->assertEquals('org_2', $result['org_code']);
        $this->assertEquals('original', $result['client_id']);
    }

    // =========================================================================
    // URL Validation Tests
    // =========================================================================

    public function testValidationURLAcceptsValidHttpsUrl(): void
    {
        $this->assertTrue((bool) Utils::validationURL('https://example.com'));
        $this->assertTrue((bool) Utils::validationURL('https://test.kinde.com'));
        $this->assertTrue((bool) Utils::validationURL('https://sub.domain.example.com'));
    }

    public function testValidationURLAcceptsValidHttpUrl(): void
    {
        $this->assertTrue((bool) Utils::validationURL('http://localhost'));
        $this->assertTrue((bool) Utils::validationURL('http://localhost:8000'));
        $this->assertTrue((bool) Utils::validationURL('http://example.com'));
    }

    public function testValidationURLAcceptsUrlWithPort(): void
    {
        $this->assertTrue((bool) Utils::validationURL('https://example.com:443'));
        $this->assertTrue((bool) Utils::validationURL('http://localhost:3000'));
        $this->assertTrue((bool) Utils::validationURL('http://localhost:8080/callback'));
    }

    public function testValidationURLAcceptsUrlWithPath(): void
    {
        $this->assertTrue((bool) Utils::validationURL('https://example.com/callback'));
        $this->assertTrue((bool) Utils::validationURL('https://example.com/api/v1'));
        $this->assertTrue((bool) Utils::validationURL('https://example.com/auth/kinde/callback'));
    }

    public function testValidationURLRejectsInvalidUrl(): void
    {
        $this->assertFalse((bool) Utils::validationURL('invalid-url'));
        $this->assertFalse((bool) Utils::validationURL('example.com'));
        $this->assertFalse((bool) Utils::validationURL('ftp://example.com'));
    }

    public function testValidationURLRejectsEmptyString(): void
    {
        $this->assertFalse((bool) Utils::validationURL(''));
    }

    // =========================================================================
    // Base64 URL Encoding Tests
    // =========================================================================

    public function testBase64UrlEncodeBasicString(): void
    {
        $input = 'Hello, World!';
        $result = Utils::base64UrlEncode($input);

        // Base64 URL encoding should not contain +, /, or =
        $this->assertStringNotContainsString('+', $result);
        $this->assertStringNotContainsString('/', $result);
        $this->assertStringNotContainsString('=', $result);
    }

    public function testBase64UrlEncodeIsReversible(): void
    {
        $input = 'Test String for Encoding';
        $encoded = Utils::base64UrlEncode($input);
        
        // Convert back from base64url to base64
        $base64 = strtr($encoded, '-_', '+/');
        $padded = str_pad($base64, strlen($base64) + (4 - strlen($base64) % 4) % 4, '=');
        $decoded = base64_decode($padded);

        $this->assertEquals($input, $decoded);
    }

    public function testBase64UrlEncodeEmptyString(): void
    {
        $result = Utils::base64UrlEncode('');
        $this->assertEquals('', $result);
    }

    // =========================================================================
    // Random String Generation Tests
    // =========================================================================

    public function testRandomStringGeneratesCorrectLength(): void
    {
        $length32 = Utils::randomString(32);
        $length64 = Utils::randomString(64);

        // The actual string length will be longer due to base64 encoding
        $this->assertNotEmpty($length32);
        $this->assertNotEmpty($length64);
        $this->assertNotEquals($length32, $length64);
    }

    public function testRandomStringGeneratesUniqueValues(): void
    {
        $strings = [];
        for ($i = 0; $i < 10; $i++) {
            $strings[] = Utils::randomString();
        }

        // All generated strings should be unique
        $unique = array_unique($strings);
        $this->assertCount(10, $unique);
    }

    // =========================================================================
    // Challenge Generation Tests
    // =========================================================================

    public function testGenerateChallengeReturnsRequiredKeys(): void
    {
        $challenge = Utils::generateChallenge();

        $this->assertArrayHasKey('state', $challenge);
        $this->assertArrayHasKey('codeVerifier', $challenge);
        $this->assertArrayHasKey('codeChallenge', $challenge);
    }

    public function testGenerateChallengeValuesAreNotEmpty(): void
    {
        $challenge = Utils::generateChallenge();

        $this->assertNotEmpty($challenge['state']);
        $this->assertNotEmpty($challenge['codeVerifier']);
        $this->assertNotEmpty($challenge['codeChallenge']);
    }

    public function testGenerateChallengeValuesAreUnique(): void
    {
        $challenge1 = Utils::generateChallenge();
        $challenge2 = Utils::generateChallenge();

        $this->assertNotEquals($challenge1['state'], $challenge2['state']);
        $this->assertNotEquals($challenge1['codeVerifier'], $challenge2['codeVerifier']);
        $this->assertNotEquals($challenge1['codeChallenge'], $challenge2['codeChallenge']);
    }

    // =========================================================================
    // Flag Type Mapping Tests
    // =========================================================================

    public function testListTypeMapping(): void
    {
        $this->assertEquals('string', Utils::$listType['s']);
        $this->assertEquals('integer', Utils::$listType['i']);
        $this->assertEquals('boolean', Utils::$listType['b']);
    }
}
