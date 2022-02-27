<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Baseline;

class ViolationBaseline
{
    /**
     * The name of the sniff
     */
    private string $sniffName;

    /**
     * The relative file path
     */
    private string $fileName;

    /**
     * The length of the filename to improve comparison performance
     */
    private int $fileNameLength;

    /**
     * The code signature for the baseline
     */
    private string $signature;

    /**
     * Initialize the violation baseline
     */
    public function __construct(string $sniffName, string $fileName, string $signature)
    {
        $this->sniffName      = $sniffName;
        $this->fileName       = $fileName;
        $this->fileNameLength = strlen($fileName);
        $this->signature      = $signature;
    }

    /**
     * Get the sniff name that was baselined
     */
    public function getSniffName(): string
    {
        return $this->sniffName;
    }

    /**
     * Get the code signature for this baseline
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Test if the given filepath matches the relative filename in the baseline
     */
    public function matches(string $filepath): bool
    {
        return substr($filepath, -$this->fileNameLength) === $this->fileName;
    }
}
