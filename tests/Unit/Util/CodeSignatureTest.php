<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Util;

use DR\CodeSnifferBaseline\Util\CodeSignature;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DR\CodeSnifferBaseline\Util\CodeSignature
 */
class CodeSignatureTest extends TestCase
{
    /**
     * @covers       ::createSignature
     * @dataProvider dataProvider
     */
    public function testCreateSignature(int $lineNr, string $expected): void
    {
        $tokens = [
            [
                'content' => 'line1',
                'line'    => 1,
            ],
            [
                'content' => 'line2',
                'line'    => 2,
            ],
            [
                'content' => 'line3',
                'line'    => 3,
            ],
            [
                'content' => "\r\n",
                'line'    => 3,
            ],
            [
                'content' => 'line4',
                'line'    => 4,
            ],
            [
                'content' => 'line5',
                'line'    => 5,
            ],
        ];

        $signature = CodeSignature::createSignature($tokens, $lineNr);
        static::assertSame($expected, $signature);
    }

    /**
     * @return array<string, array<int|string>>
     */
    public function dataProvider(): array
    {
        return [
            'first line of file'  => [
                1,
                hash('sha1', 'line1line2'),
            ],
            'middle line of file' => [
                3,
                hash('sha1', 'line2line3line4'),
            ],
            'last line of file'   => [
                5,
                hash('sha1', 'line4line5'),
            ],
        ];
    }
}
