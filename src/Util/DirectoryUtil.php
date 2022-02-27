<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Util;

use RuntimeException;

class DirectoryUtil
{
    public static function getProjectRoot(): string
    {
        return dirname(self::getVendorDir()) . '/';
    }

    public static function getVendorDir(): string
    {
        $paths = [
            // when php-codesniffer-baseline is installed as package in a project
            dirname(__DIR__, 5) . '/vendor/',
            // when running from the project itself
            dirname(__DIR__, 2) . '/vendor/'
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        // @codeCoverageIgnoreStart
        throw new RuntimeException('Unable to find /vendor/ directory');
        // @codeCoverageIgnoreEnd
    }
}
