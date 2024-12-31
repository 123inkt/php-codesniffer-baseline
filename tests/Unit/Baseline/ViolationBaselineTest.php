<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Baseline;

use PHPUnit\Framework\Attributes\CoversClass;
use DR\CodeSnifferBaseline\Baseline\ViolationBaseline;
use PHPUnit\Framework\TestCase;

#[CoversClass(ViolationBaseline::class)]
class ViolationBaselineTest extends TestCase
{
    public function testAccessors(): void
    {
        $violation = new ViolationBaseline('sniff', 'foobar', 'signature');
        static::assertSame('sniff', $violation->getSniffName());
        static::assertSame('signature', $violation->getSignature());
    }

    /**
     * Test the give file matches the baseline correctly
     */
    public function testMatches(): void
    {
        $violation = new ViolationBaseline('sniff', 'foobar.txt', 'signature');
        static::assertTrue($violation->matches('foobar.txt'));
        static::assertTrue($violation->matches('/test/foobar.txt'));
        static::assertFalse($violation->matches('foo.txt'));
    }
}
