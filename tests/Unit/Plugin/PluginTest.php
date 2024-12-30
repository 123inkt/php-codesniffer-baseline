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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Plugin::class)]
class PluginTest extends TestCase
{
    private Composer&MockObject $composer;
    private IOInterface&MockObject $stream;

    protected function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->stream   = $this->createMock(IOInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $expected = [
            ScriptEvents::POST_INSTALL_CMD => [['onPostInstall', 0]],
            ScriptEvents::POST_UPDATE_CMD  => [['onPostInstall', 0]],
        ];

        static::assertSame($expected, Plugin::getSubscribedEvents());
    }

    public function testOnPostInstallWithoutExistingFile(): void
    {
        $this->stream->expects(static::once())->method('error')->with(static::stringContains('failed to find'));

        $plugin = new Plugin('/tmp/foobar.txt');
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();
    }

    public function testOnPostInstallAlreadyContainsInjection(): void
    {
        $matcher = static::exactly(2);
        $this->stream->expects($matcher)
            ->method('info')->willReturnCallback(function (...$parameters) use ($matcher) {
                static::assertIsString($parameters[0]);
                if ($matcher->numberOfInvocations() === 1) {
                    static::assertStringContainsString('read', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    static::assertStringContainsString('is already modified', $parameters[0]);
                }
            });

        $file = vfsStream::setup()->url() . '/File.php';
        file_put_contents($file, 'foobar \\' . BaselineHandler::class . 'foobar');

        $plugin = new Plugin($file);
        $plugin->onPostInstall();
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();
    }

    public function testOnPostInstallShouldErrorWhenMessageCountCantBeFound(): void
    {
        $matcher = static::once();
        $this->stream->expects($matcher)
            ->method('error')
            ->willReturnCallback(function (...$parameters) use ($matcher) {
                static::assertIsString($parameters[0]);
                if ($matcher->numberOfInvocations() === 1) {
                    static::assertStringContainsString('unable to find `$messageCount++;`', $parameters[0]);
                }
            });

        $file = vfsStream::setup()->url() . '/File.php';
        file_put_contents($file, 'foobar ');

        $plugin = new Plugin($file);
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();
    }

    public function testOnPostInstallShouldInjectCode(): void
    {
        $matcher = static::exactly(2);
        $this->stream->expects($matcher)
            ->method('info')->willReturnCallback(function (...$parameters) use ($matcher) {
                static::assertIsString($parameters[0]);
                if ($matcher->numberOfInvocations() === 1) {
                    static::assertStringContainsString('read', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    static::assertStringContainsString('saved to:', $parameters[0]);
                }
            });

        $file = vfsStream::setup()->url() . '/File.php';
        file_put_contents($file, 'foobar $messageCount++; foobar');

        $plugin = new Plugin($file);
        $plugin->activate($this->composer, $this->stream);
        $plugin->onPostInstall();

        static::assertStringContainsString('\\' . BaselineHandler::class, (string)file_get_contents($file));
    }

    /**
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
