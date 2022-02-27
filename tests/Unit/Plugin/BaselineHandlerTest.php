<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Plugin;

use DR\CodeSnifferBaseline\Baseline\BaselineSet;
use DR\CodeSnifferBaseline\Plugin\BaselineHandler;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DR\CodeSnifferBaseline\Plugin\BaselineHandler
 * @covers ::__construct
 */
class BaselineHandlerTest extends TestCase
{
    /**
     * @covers ::isSuppressed
     */
    public function testIsSuppressedNoBaselineShouldBeFalse(): void
    {
        $handler = new BaselineHandler(null);
        static::assertFalse($handler->isSuppressed([], 1, 'foobar', '/path/'));
    }

    /**
     * @covers ::isSuppressed
     */
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
