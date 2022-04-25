<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Plugin;

use DR\CodeSnifferBaseline\Baseline\BaselineSet;
use DR\CodeSnifferBaseline\Baseline\BaselineSetFactory;
use DR\CodeSnifferBaseline\Util\CodeSignature;
use DR\CodeSnifferBaseline\Util\DirectoryUtil;
use PHP_CodeSniffer\Config;

class BaselineHandler
{
    private ?BaselineSet $baseline;

    private static ?BaselineHandler $instance = null;

    public function __construct(?BaselineSet $baseline)
    {
        $this->baseline = $baseline;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getInstance(Config $config): self
    {
        // singleton is required to hook into the php-codesniffer code.
        if (self::$instance === null) {
            $baseline = null;
            // only read baseline if phpcs is not writing one.
            if ($config->reportFile === null || strpos($config->reportFile, 'phpcs.baseline.xml') === false) {
                $baseline = BaselineSetFactory::fromFile(DirectoryUtil::getProjectRoot() . 'phpcs.baseline.xml');
            }

            self::$instance = new self($baseline);
        }

        return self::$instance;
    }

    /**
     * @param array<int|string, array{line: int, content?: string}> $tokens All tokens of a given file.
     */
    public function isSuppressed(array $tokens, int $lineNr, string $sniffCode, string $path): bool
    {
        if ($this->baseline === null) {
            return false;
        }

        return $this->baseline->contains($sniffCode, $path, CodeSignature::createSignature($tokens, $lineNr));
    }
}
