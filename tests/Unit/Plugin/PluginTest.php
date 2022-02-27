<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use DR\CodeSnifferBaseline\Plugin\BaselineHandler;
use DR\CodeSnifferBaseline\Plugin\Plugin;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\CodeSnifferBaseline\Plugin\Plugin
 * @covers ::__construct
 */
class PluginTest extends TestCase
{
    /** @var Composer|MockObject */
    private Composer $composer;
    /** @var IOInterface|MockObject */
    private IOInterface $stream;

    protected function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->stream   = $this->createMock(IOInterface::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $expected = [
            ScriptEvents::POST_INSTALL_CMD => [['onPostInstall', 0]],
            ScriptEvents::POST_UPDATE_CMD  => [['onPostInstall', 0]],
        ];

        static::assertSame($expected, Plugin::getSubscribedEvents());
    }

    /**
     * @covers ::activate
     * @covers ::onPostInstall
     */
    public function testOnPostInstallWithoutExistingFile(): void
    {
        $this->stream->expects(static::once())->method('error')->with(static::stringContains('failed to find'));

        $plugin = new Plugin('/tmp/foobar.txt');
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();
    }

    /**
     * @covers ::activate
     * @covers ::onPostInstall
     */
    public function testOnPostInstallAlreadyContainsInjection(): void
    {
        $this->stream->expects(static::exactly(2))
            ->method('info')
            ->withConsecutive([static::stringContains('read')], [static::stringContains('is already modified')]);

        $file = vfsStream::setup()->url() . '/File.php';
        file_put_contents($file, 'foobar \\' . BaselineHandler::class . 'foobar');

        $plugin = new Plugin($file);
        $plugin->onPostInstall();
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();
    }

    /**
     * @covers ::activate
     * @covers ::onPostInstall
     */
    public function testOnPostInstallShouldErrorWhenMessageCountCantBeFound(): void
    {
        $this->stream->expects(static::once())
            ->method('error')
            ->withConsecutive([static::stringContains('unable to find `$messageCount++;`')]);

        $file = vfsStream::setup()->url() . '/File.php';
        file_put_contents($file, 'foobar ');

        $plugin = new Plugin($file);
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();
    }

    /**
     * @covers ::activate
     * @covers ::onPostInstall
     */
    public function testOnPostInstallShouldInjectCode(): void
    {
        $this->stream->expects(static::exactly(2))
            ->method('info')
            ->withConsecutive([static::stringContains('read')], [static::stringContains('saved to:')]);

        $file = vfsStream::setup()->url() . '/File.php';
        file_put_contents($file, 'foobar $messageCount++; foobar');

        $plugin = new Plugin($file);
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();

        static::assertStringContainsString('\\' . BaselineHandler::class, (string)file_get_contents($file));
    }

    /**
     * @covers ::run
     * @throws Exception
     */
    public function testRun(): void
    {
        $this->stream->expects(static::once())
            ->method('info')
            ->with(static::stringContains('read'))
            ->willThrowException(new RuntimeException('foobar'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('foobar');
        Plugin::run(new Event('foobar', $this->composer, $this->stream));
    }
}
