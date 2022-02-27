<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Reports;

use DR\CodeSnifferBaseline\Util;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reports\Report;
use XMLWriter;

class Baseline implements Report
{
    /**
     * @phpstan-param array{
     *     errors: int,
     *     warnings: int,
     *     filename: string,
     *     messages: array<int, string[][][]>
     * } $report
     * @inheritDoc
     */
    public function generateFileReport($report, File $phpcsFile, $showSources = false, $width = 80): bool
    {
        $out = new XMLWriter();
        $out->openMemory();
        $out->setIndent(true);
        $out->setIndentString('    ');
        $out->startDocument('1.0', 'UTF-8');

        if ($report['errors'] === 0 && $report['warnings'] === 0) {
            // Nothing to print.
            return false;
        }

        foreach ($report['messages'] as $lineNr => $lineErrors) {
            $signature = Util\CodeSignature::createSignature($phpcsFile->getTokens(), $lineNr);

            foreach ($lineErrors as $colErrors) {
                foreach ($colErrors as $error) {
                    $out->startElement('violation');
                    $out->writeAttribute('file', $report['filename']);
                    $out->writeAttribute('sniff', $error['source']);
                    $out->writeAttribute('signature', $signature);

                    $out->endElement();
                }
            }
        }

        // Remove the start of the document because we will
        // add that manually later. We only have it in here to
        // properly set the encoding.
        $content = $out->flush();
        $content = preg_replace("/[\n\r]/", PHP_EOL, $content);
        $content = substr($content, (int)strpos($content, PHP_EOL) + strlen(PHP_EOL));

        echo $content;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function generate(
        $cachedData,
        $totalFiles,
        $totalErrors,
        $totalWarnings,
        $totalFixable,
        $showSources = false,
        $width = 80,
        $interactive = false,
        $toScreen = true
    ): void {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<phpcs-baseline version="' . Config::VERSION . '">';

        // Split violations on line-ending, make them unique and sort them.
        if ($cachedData !== "") {
            $lines = explode(PHP_EOL, $cachedData);
            $lines = array_unique($lines);
            sort($lines);
            $cachedData = implode(PHP_EOL, $lines);
        }

        echo $cachedData;
        echo PHP_EOL . '</phpcs-baseline>' . PHP_EOL;
    }
}
