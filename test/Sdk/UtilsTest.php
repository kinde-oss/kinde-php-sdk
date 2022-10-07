<?php

use Kinde\KindeSDK\Sdk\Utils\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * It generates a random string of 28 characters.
     */
    public function test_random_string()
    {
        $result = Utils::randomString(28);
        $this->assertNotEmpty($result);
    }

    /**
     * It generates a challenge for the user to complete.
     */
    public function test_generate_challenge()
    {
        $result = Utils::generateChallenge();
        $this->assertArrayHasKey('state', $result);
        $this->assertArrayHasKey('codeVerifier', $result);
        $this->assertArrayHasKey('codeChallenge', $result);
    }

    /**
     * It checks if the url is valid or not.
     */
    public function test_validation_url()
    {
        $url = 'https://test.com';
        $result = Utils::validationURL($url);
        $this->assertEquals($result, 1);
    }

    /**
     * It checks if the url is valid or not.
     */
    public function test_validation_url_invalid()
    {
        $urlInvalid = 'test.c';
        $result = Utils::validationURL($urlInvalid);
        $this->assertNotEquals($result, 1);
    }
}