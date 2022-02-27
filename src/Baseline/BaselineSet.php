<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Baseline;

class BaselineSet
{
    /** @var array<string, array<string, ViolationBaseline[]>> */
    private array $violations = [];

    /**
     * Add a single entry to the baseline set
     */
    public function addEntry(ViolationBaseline $entry): void
    {
        $this->violations[$entry->getSniffName()][$entry->getSignature()][] = $entry;
    }

    /**
     * Test if the given sniff and filename is in the baseline collection
     */
    public function contains(string $sniffName, string $fileName, string $signature): bool
    {
        if (isset($this->violations[$sniffName][$signature]) === false) {
            return false;
        }

        // Normalize slashes in file name.
        $fileName = str_replace('\\', '/', $fileName);

        foreach ($this->violations[$sniffName][$signature] as $baseline) {
            if ($baseline->matches($fileName) === true) {
                return true;
            }
        }

        return false;
    }
}
