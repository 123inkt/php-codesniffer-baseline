<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Baseline;

use RuntimeException;

class BaselineSetFactory
{
    /**
     * Read the baseline violations from the given filename path.
     * @throws RuntimeException
     */
    public static function fromFile(string $fileName): ?BaselineSet
    {
        if (file_exists($fileName) === false) {
            return null;
        }

        $xml = @simplexml_load_string((string)file_get_contents($fileName));
        if ($xml === false) {
            throw new RuntimeException('Unable to read xml from: ' . $fileName);
        }

        $baselineSet = new BaselineSet();

        foreach ($xml->children() as $node) {
            if ($node->getName() !== 'violation') {
                continue;
            }

            if (isset($node['sniff']) === false) {
                throw new RuntimeException('Missing `sniff` attribute in `violation` in ' . $fileName);
            }

            if (isset($node['file']) === false) {
                throw new RuntimeException('Missing `file` attribute in `violation` in ' . $fileName);
            }

            if (isset($node['signature']) === false) {
                throw new RuntimeException('Missing `signature` attribute in `violation` in ' . $fileName);
            }

            // Normalize filepath (if needed).
            $filePath = '/' . ltrim(str_replace('\\', '/', (string)$node['file']), '/');

            $baselineSet->addEntry(new ViolationBaseline((string)$node['sniff'], $filePath, (string)$node['signature']));
        }

        return $baselineSet;
    }
}
