<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use DR\CodeSnifferBaseline\Baseline\BaselineSet;
use DR\CodeSnifferBaseline\Plugin\BaselineHandler;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaselineHandler::class)]
class BaselineHandlerTest extends TestCase
{
    public function testIsSuppressedNoBaselineShouldBeFalse(): void
    {
        $handler = new BaselineHandler(null);
        static::assertFalse($handler->isSuppressed([], 1, 'foobar', '/path/'));
    }

    public function testIsSuppressedWithBaseline(): void
    {
        $baseline = $this->createMock(BaselineSet::class);
        $baseline->expects(static::once())
            ->method('contains')
            ->with('foobar', '/path/', 'da39a3ee5e6b4b0d3255bfef95601890afd80709')
            ->willReturn(true);

        $handler = new BaselineHandler($baseline);
        static::assertTrue($handler->isSuppressed([], 1, 'foobar', '/path/'));
    }
}
