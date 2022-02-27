<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Util;

class CodeSignature
{
    /**
     * Generate the sha1 code signature for the tokens around the given line.
     *
     * @param array<int|string, array> $tokens All tokens of a given file.
     */
    public static function createSignature(array $tokens, int $lineNr): string
    {
        // Get all tokens one line before and after.
        $start = $lineNr - 1;
        $end   = $lineNr + 1;

        $content = '';
        foreach ($tokens as $token) {
            if ($token['line'] > $end) {
                break;
            }

            // Concat content excluding line endings.
            if ($token['line'] >= $start && isset($token['content']) === true) {
                $content .= trim($token['content'], "\r\n");
            }
        }

        // Generate sha1 hash.
        return hash('sha1', $content);
    }
}
