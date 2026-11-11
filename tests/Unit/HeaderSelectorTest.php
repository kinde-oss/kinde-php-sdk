<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\HeaderSelector;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;

/**
 * @covers \Kinde\KindeSDK\HeaderSelector
 */
class HeaderSelectorTest extends KindeTestCase
{
    public function testStripsCharsetFromJsonAcceptHeader(): void
    {
        $selector = new HeaderSelector();
        $accept = function (array $types) use ($selector) {
            return $selector->selectHeaders($types, '', false)['Accept'];
        };

        $this->assertSame('application/json', $accept(['application/json; charset=utf-8', 'application/json']));
        $this->assertSame('application/json', $accept(['application/json; charset=utf-8']));
        $this->assertSame('application/json,text/html;q=0.9', $accept(['text/html', 'application/json; charset=utf-8']));
    }
}
