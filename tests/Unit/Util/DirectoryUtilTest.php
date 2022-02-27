<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Util;

use DR\CodeSnifferBaseline\Util\DirectoryUtil;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DR\CodeSnifferBaseline\Util\DirectoryUtil
 */
class DirectoryUtilTest extends TestCase
{
    /**
     * @covers ::getVendorDir
     */
    public function testGetVendorDir(): void
    {
        $directory = DirectoryUtil::getVendorDir();

        static::assertSame(dirname(__DIR__, 3) . '/vendor/', $directory);
    }

    /**
     * @covers ::getProjectRoot
     */
    public function testGetProjectRoot(): void
    {
        $directory = DirectoryUtil::getProjectRoot();

        static::assertSame(dirname(__DIR__, 3) . '/', $directory);
    }
}
