<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Baseline;

use DR\CodeSnifferBaseline\Baseline\BaselineSetFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\CodeSnifferBaseline\Baseline\BaselineSetFactory
 */
class BaselineSetFactoryTest extends TestCase
{
    /**
     * @covers ::fromFile
     */
    public function testFromFileShouldSucceed(): void
    {
        $filename = __DIR__ . '/TestFiles/baseline.xml';
        $set      = BaselineSetFactory::fromFile($filename);
        static::assertNotNull($set);
        static::assertTrue($set->contains('Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen', '/test/src/foo/bar', 'foobar'));
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFileShouldSucceedWithBackAndForwardSlashes(): void
    {
        $filename = __DIR__ . '/TestFiles/baseline.xml';
        $set      = BaselineSetFactory::fromFile($filename);

        static::assertNotNull($set);
        static::assertTrue($set->contains('Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen', '/test\\src\\foo/bar', 'foobar'));
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFileShouldReturnNullIfAbsent(): void
    {
        static::assertNull(BaselineSetFactory::fromFile('foobar.xml'));
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFileShouldThrowExceptionForOnInvalidXML(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to read xml from');
        BaselineSetFactory::fromFile(__DIR__ . '/TestFiles/invalid-baseline.xml');
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFileViolationMissingSniffShouldThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing `sniff` attribute in `violation`');
        BaselineSetFactory::fromFile(__DIR__ . '/TestFiles/missing-sniff-baseline.xml');
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFileViolationMissingSignatureShouldThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing `signature` attribute in `violation` in');
        BaselineSetFactory::fromFile(__DIR__ . '/TestFiles/missing-signature-baseline.xml');
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFileViolationMissingFileShouldThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing `file` attribute in `violation` in');
        BaselineSetFactory::fromFile(__DIR__ . '/TestFiles/missing-file-baseline.xml');
    }
}
