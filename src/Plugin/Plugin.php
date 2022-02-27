<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Plugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use DR\CodeSnifferBaseline\Util\DirectoryUtil;
use Exception;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private ?IOInterface $stream = null;
    private string       $codeSnifferFilePath;

    public function __construct(?string $codeSnifferFilePath = null)
    {
        $this->codeSnifferFilePath = $codeSnifferFilePath ?? DirectoryUtil::getVendorDir() . 'squizlabs/php_codesniffer/src/Files/File.php';
    }

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $stream): void
    {
        $this->stream = $stream;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function deactivate(Composer $composer, IOInterface $stream): void
    {
        // not necessary
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function uninstall(Composer $composer, IOInterface $stream): void
    {
        // not necessary
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator) - handled by the === false check
     */
    public function onPostInstall(): void
    {
        if ($this->stream === null) {
            return;
        }

        // find code sniffer File
        if (file_exists($this->codeSnifferFilePath) === false) {
            $this->stream->error('php-codesniffer-baseline: failed to find: ' . $this->codeSnifferFilePath);

            return;
        }

        // read file contents of src/Files/File.php
        $source = @file_get_contents($this->codeSnifferFilePath);
        // @codeCoverageIgnoreStart
        if ($source === false) {
            $this->stream->error('php-codesniffer-baseline: failed to read contents of: ' . $this->codeSnifferFilePath);

            return;
        }
        // @codeCoverageIgnoreEnd
        $this->stream->info('php-codesniffer-baseline: read: ' . $this->codeSnifferFilePath);

        if (str_contains($source, BaselineHandler::class)) {
            $this->stream->info('php-codesniffer-baseline: ignored. src/Files/File.php is already modified');

            return;
        }

        $search = '$messageCount++;';
        if (str_contains($source, $search) === false) {
            $this->stream->error('php-codesniffer-baseline: unable to find `' . $search . '` in `squizlabs/php_codesniffer/src/Files/File.php`');

            return;
        }

        // Upon composer install or update, inject a single line of code into `squizlabs/php_codesniffer/src/Files/File.php`
        // This is a fragile solution, but necessary until PR:3387 (https://github.com/squizlabs/PHP_CodeSniffer/pull/3387)
        // is accepted.
        $code   = 'if (\\' . BaselineHandler::class . '::getInstance($this->config)';
        $code   .= '->isSuppressed($this->getTokens(), $line, $sniffCode, $this->path)) {';
        $code   .= 'return false;}';
        $source = str_replace($search, $code . "\n\n        " . $search, $source);

        // write back to src/Files/File.php
        file_put_contents($this->codeSnifferFilePath, $source);
        $this->stream->info('php-codesniffer-baseline: saved to: ' . $this->codeSnifferFilePath);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [['onPostInstall', 0]],
            ScriptEvents::POST_UPDATE_CMD  => [['onPostInstall', 0]],
        ];
    }

    /**
     * Triggers the plugin's main functionality.
     * Makes it possible to run the plugin as a custom command.
     * @throws Exception
     */
    public static function run(Event $event): void
    {
        $instance         = new self();
        $instance->stream = $event->getIO();
        $instance->onPostInstall();
        // @codeCoverageIgnoreStart
    }
}// @codeCoverageIgnoreEnd
