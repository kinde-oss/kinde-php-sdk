<?php

namespace Kinde\KindeSDK\Tests\Unit;

use InvalidArgumentException;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testCheckAdditionalParametersAcceptsValidInputs(): void
    {
        $params = [
            'audience' => 'https://test-domain.kinde.com/api',
            'org_code' => 'org_123',
            'prompt' => 'login',
            'redirect_uri' => 'https://example.com/callback',
        ];

        $this->assertSame($params, Utils::checkAdditionalParameters($params));
    }

    public function testCheckAdditionalParametersRejectsUnknownKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide correct additional, unknown_key');

        Utils::checkAdditionalParameters(['unknown_key' => 'value']);
    }

    public function testCheckAdditionalParametersRejectsWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid audience. Expected: string');

        Utils::checkAdditionalParameters(['audience' => 123]);
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
}

