<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use DR\CodeSnifferBaseline\Util\DirectoryUtil;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryUtil::class)]
class DirectoryUtilTest extends TestCase
{
    public function testGetVendorDir(): void
    {
        $directory = DirectoryUtil::getVendorDir();

        static::assertSame(dirname(__DIR__, 3) . '/vendor/', $directory);
    }

    public function testGetProjectRoot(): void
    {
        $directory = DirectoryUtil::getProjectRoot();

        static::assertSame(dirname(__DIR__, 3) . '/', $directory);
    }
}
