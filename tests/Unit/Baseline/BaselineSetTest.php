<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Baseline;

use PHPUnit\Framework\Attributes\CoversClass;
use DR\CodeSnifferBaseline\Baseline\BaselineSet;
use DR\CodeSnifferBaseline\Baseline\ViolationBaseline;
use PHPUnit\Framework\TestCase;

/**
 * Test the logic of the baseline set
 */
#[CoversClass(BaselineSet::class)]
class BaselineSetTest extends TestCase
{
    public function testSetContainsEntry(): void
    {
        $set = new BaselineSet();
        $set->addEntry(new ViolationBaseline('sniff', 'foobar', 'signature'));

        static::assertTrue($set->contains('sniff', 'foobar', 'signature'));
    }

    public function testShouldFindEntryForIdenticalRules(): void
    {
        $set = new BaselineSet();
        $set->addEntry(new ViolationBaseline('sniff', 'foo', 'signA'));
        $set->addEntry(new ViolationBaseline('sniff', 'bar', 'signB'));

        static::assertTrue($set->contains('sniff', 'foo', 'signA'));
        static::assertTrue($set->contains('sniff', 'bar', 'signB'));
        static::assertFalse($set->contains('sniff', 'unknown', 'signA'));
        static::assertFalse($set->contains('sniff', 'foo', 'signB'));
    }

    public function testShouldNotFindEntryForNonExistingRule(): void
    {
        $set = new BaselineSet();
        $set->addEntry(new ViolationBaseline('sniff', 'foo', 'signature'));

        static::assertFalse($set->contains('unknown', 'foo', 'signature'));
    }
}
