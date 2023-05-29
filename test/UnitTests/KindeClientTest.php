<?php

use Kinde\KindeSDK\Test\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\Enums\TokenType;
use Kinde\KindeSDK\Test\Sdk\KindeClientSDK;
use Kinde\KindeSDK\Test\Sdk\Storage\Storage;
use PHPUnit\Framework\TestCase;

class KindeClientSDKTest extends TestCase
{
    private $client;

    private $domain;

    private $redirectUri;

    private $logoutRedirectUri;

    private $clientId;

    private $clientSecret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->domain = $_ENV['KINDE_HOST'];

        $this->redirectUri = $_ENV['KINDE_REDIRECT_URI'];

        $this->clientId = $_ENV['KINDE_CLIENT_ID'];

        $this->clientSecret = $_ENV['KINDE_CLIENT_SECRET'];

        $this->logoutRedirectUri = $_ENV['KINDE_POST_LOGOUT_REDIRECT_URL'];

        // $stub->method('setItem')
        //     ->with($this->exactly(1))
        //     ->willReturn('foo');
    }

    /**
     * `test_initial` tests the initialisation of the KindeClientSDK class
     */
    public function test_initial(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $this->assertInstanceOf(KindeClientSDK::class, $client);
    }

    /**
     * `test_initial_empty_domain` tests that an exception is thrown when the domain is empty
     */
    public function test_initial_empty_domain(): void
    {
        $this->expectExceptionMessage("Please provide domain");
        new KindeClientSDK('', '', '', '', '', '');
    }

    /**
     * This function tests that the KindeClientSDK class throws an exception when the redirect_uri is
     * not provided
     */
    public function test_initial_empty_redirect_uri(): void
    {
        $this->expectExceptionMessage("Please provide redirect_uri");
        $this->client = new KindeClientSDK($this->domain, '', $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    public function test_initial_empty_client_id(): void
    {
        $this->expectExceptionMessage("Please provide client_id");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, '', $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    public function test_initial_empty_client_secret(): void
    {
        $this->expectExceptionMessage("Please provide client_secret");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, '', GrantType::PKCE, $this->logoutRedirectUri);
    }

    /**
     * `test_initial_invalid_domain` tests that the `KindeClientSDK` class throws an exception when an
     * invalid domain is provided
     */
    public function test_initial_invalid_domain(): void
    {
        $this->expectExceptionMessage("Please provide valid domain");
        $this->client = new KindeClientSDK('test.c', $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    /**
     * It tests the initial invalid redirect_uri.
     */
    public function test_initial_invalid_redirect_uri(): void
    {
        $this->expectExceptionMessage("Please provide valid redirect_uri");
        $this->client = new KindeClientSDK($this->domain, 'test.c', $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    public function test_initial_valid_audience(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $this->assertInstanceOf(KindeClientSDK::class, $this->client);
    }

    public function test_initial_invalid_audience(): void
    {
        $this->expectExceptionMessage("Please supply a valid audience. Expected: string");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri, '', ['audience' => 1233]);
    }

    public function test_get_is_authenticated(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $this->assertIsBool($client->isAuthenticated);
        $this->assertEmpty($client->isAuthenticated);
    }

    public function test_login_invalid_org_code(): void
    {
        $this->expectExceptionMessage("Please supply a valid org_code. Expected: string");

        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code' => 123,
            'org_name' => 'My Application',
        ];
        $client->login($additional);
        // $mock = \Mockery::mock('alias:BaseStorage');
        // // $stub::st
        // // $stub->method('setItem')->willReturn('ok');
        // $client->login($additional);
    }

    public function test_login_invalid_org_name(): void
    {
        $this->expectExceptionMessage("Please supply a valid org_name. Expected: string");
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code' => '123',
            'org_name' => 123
        ];
        $client->login($additional);
    }

    public function test_login_invalid_additional_org_name(): void
    {
        $this->expectExceptionMessage("Please provide correct additional, org_name_test");
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code' => '123',
            'org_name_test' => '123'
        ];
        $client->login($additional);
    }

    public function test_login_invalid_additional_org_code(): void
    {
        $this->expectExceptionMessage("Please provide correct additional, org_code_test");
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code_test' => '123',
            'org_name' => '123'
        ];
        $client->login($additional);
    }

    public function test_claim_helper_require_authenticate(): void
    {
        $this->expectExceptionMessage("Request is missing required authentication credential");

        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $client->getClaim('something');
    }

    public function test_claim_helper_authenticated(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ']);
        $this->assertEquals("https://trung.kinde.com", $client->getClaim('iss')['value']);
        $this->assertEquals("iss", $client->getClaim('iss')['name']);
    }

    public function test_flag_helper_get_boolean_not_found(): void
    {
        $this->expectExceptionMessage("This flag 'iss' was not found, and no default value has been provided");

        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ']);
        $client->getBooleanFlag('iss');
    }

    public function test_flag_helper_get_boolean_provide_default_value(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ']);

        $this->assertEquals(true, $client->getBooleanFlag('iss', true)['value']);
        $this->assertEquals('iss', $client->getBooleanFlag('iss', true)['code']);
        $this->assertEquals('boolean', $client->getBooleanFlag('iss', true)['type']);
        $this->assertEquals(true, $client->getBooleanFlag('iss', true)['is_default']);
    }

    public function test_flag_helper_get_boolean(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ']);
        $this->assertEquals(true, $client->getBooleanFlag('enable_dark_theme')['value']);
        $this->assertEquals('enable_dark_theme', $client->getBooleanFlag('enable_dark_theme')['code']);
        $this->assertEquals('boolean', $client->getBooleanFlag('enable_dark_theme')['type']);
        $this->assertEquals(false, $client->getBooleanFlag('enable_dark_theme')['is_default']);
    }

    public function test_check_is_not_authenticated(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ']);
        $this->assertEquals(false, $client->isAuthenticated);
    }

    public function test_check_is_authenticated(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ', 'should_valid' => true]);
        $this->assertEquals(false, $client->isAuthenticated);
    }

    public function test_check_is_authenticated_use_refresh_token(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['refresh_token' => 'some_value', 'access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdWQiOltdLCJhenAiOiJiOTg0ZWFiMmM1YmU0ZWU1OWEyYTAxMmZmNzdiNTJjMCIsImV4cCI6MTY4MTk4MjA4MywiZmVhdHVyZV9mbGFncyI6eyJlbmFibGVfZGFya190aGVtZSI6eyJ0IjoiYiIsInYiOnRydWV9fSwiaWF0IjoxNjgxODk1NjgyLCJpc3MiOiJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImp0aSI6ImMyYWVhYzVmLWVjZDktNDFjOS05MDU0LTc0MWU2ZmJmNzljMyIsIm9yZ19jb2RlIjoib3JnX2U1ZjI4ZTE2NzZkIiwicGVybWlzc2lvbnMiOlsicmVhZDpwcm9maWxlIiwiY3JlYXRlOnVzZXJfMSJdLCJzY3AiOlsib3BlbmlkIiwicHJvZmlsZSIsImVtYWlsIiwib2ZmbGluZSJdLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiJ9.MgXOfcAu7tV-3-QgHqwbUOL6jo2nPXdU5FifC98pbJIf2hNv8ZqmF4uTKEOv-ffkimhjjyOZDwlCb8EGHSxrXaakf31xYmkLtybPILL_KPBpK1PTBloidiRQFumoXlozgqJHDSIRemGHvtV2Mn7Z-Fg1W8duEWlWJHU_kTLhOlXGAy44IFpV_zvdwxEFjscnp621g1Ue0fdyTjMTW-3tMz-HBV87vpGKkvu3UlQDmYHrVAge03YVWQrcKdSDF-Cnud1TKpKkL6QGwp4dfoq8fQbW_6QZt_xgtivTAdfaMLFceXIZVB3MT5TUTrZUpxohPxz8DjRTWb5S8xiVvx-ygQ']);
        $this->assertEquals(true, $client->isAuthenticated);
    }

    public function test_get_claim_by_id_token(): void
    {
        $client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);

        $storage = Storage::getInstance();
        $storage->setToken(['id_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImVhOjYxOjBkOmY0Ojk2OjE3OmQ5OjIwOjk4OjBiOjNiOjcxOjU4OmQ0OjBkOjMwIiwidHlwIjoiSldUIn0.eyJhdF9oYXNoIjoiSk5XMFEzTFJqc1gyaGZrRDR2OW5CdyIsImF1ZCI6WyJodHRwczovL3RydW5nLmtpbmRlLmNvbSIsImI5ODRlYWIyYzViZTRlZTU5YTJhMDEyZmY3N2I1MmMwIl0sImF1dGhfdGltZSI6MTY4MTg5NTY4MiwiYXpwIjoiYjk4NGVhYjJjNWJlNGVlNTlhMmEwMTJmZjc3YjUyYzAiLCJlbWFpbCI6InVzZXJ0ZXN0aW5nQHlvcG1haWwuY29tIiwiZXhwIjoxNjgxODk5MjgyLCJmYW1pbHlfbmFtZSI6InRlc3QiLCJnaXZlbl9uYW1lIjoidXNlciIsImlhdCI6MTY4MTg5NTY4MiwiaXNzIjoiaHR0cHM6Ly90cnVuZy5raW5kZS5jb20iLCJqdGkiOiIyNDVlMGMxNS1jMTlmLTQ3YzItYWQ1Ni04MzY4MmZmOGNiNWQiLCJuYW1lIjoidXNlciB0ZXN0Iiwib3JnX2NvZGVzIjpbIm9yZ185ZTQwN2Y0MDY5YzEiLCJvcmdfOTBkOWZkNDI1MjhmIiwib3JnXzI4OTRjYTM0ZmJkMiIsIm9yZ18xMDc5NDkzNjUyZTciLCJvcmdfZTVmMjhlMTY3NmQiLCJvcmdfYTdiY2MwMzg1ZGVmIiwib3JnXzI4YzI0OTY3MWU0NSJdLCJwaWN0dXJlIjpudWxsLCJzdWIiOiJrcDo1OGVjZTlmNjhhN2M0YzA5OGVmYzFjZjQ1Yzc3NGUxNiIsInVwZGF0ZWRfYXQiOjEuNjgxODk1NjgyZSswOX0.VfJ0Zqj1zOlNeszTIOEn95w3EHoC24prOOPtnyP60sfWA70NRKJTqMg9csi5rrOvRPR2ipV_0w0-M5ajF6vKRUXbxa_c3GDiOFLV7hsArKB8Uhs6WENwTKI7iIvUINA9rCEi9GOqurwySLBDFwCrE5q8XXRToV1qWwcpWYQuEd7dFw1CQbGaYaYne7Azg9wb0uDXz4BwYyntAuEkg5FyBXY2D_sVHsdfmTi7ESNPhSqeC5YJCO_i-4FZh-EjLaiwUEFIPQmvdNEDsT8W1cc42TTL80rsyV7xIGnFuXLqUcz9WESFRYvjYZonnDtB86RqcaLtBOwz92E-KoseJSKE7A', 'should_valid' => true]);
        $this->assertEquals("user", $client->getClaim('given_name', TokenType::ID_TOKEN)['value']);
        $this->assertEquals("given_name", $client->getClaim('given_name', TokenType::ID_TOKEN)['name']);

        $this->assertEquals("test", $client->getClaim('family_name', TokenType::ID_TOKEN)['value']);
        $this->assertEquals("family_name", $client->getClaim('family_name', TokenType::ID_TOKEN)['name']);
    }
}
