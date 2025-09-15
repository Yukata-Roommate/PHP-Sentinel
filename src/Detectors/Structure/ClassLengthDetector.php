<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Structure;

use Sentinel\Detectors\Foundation\StructureDetector;

use Sentinel\Contracts\Analyzer;

use Sentinel\Nodes\FunctionNode;

/**
 * Class Length Detector
 *
 * @package Sentinel\Detectors\Structure
 */
class ClassLengthDetector extends StructureDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Class Length Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        foreach ($analyzer->classes() as $class) {
            $lineCount = $class->lineCount();

            if ($lineCount === null) continue;

            if ($lineCount <= $this->maxClassLength) continue;

            $this->addIssue(
                $relativePath,
                $class->start(),
                sprintf(
                    "Class \"%s\" is too long. (%d lines, max: %d)",
                    $class->name(),
                    $lineCount,
                    $this->maxClassLength
                )
            );
        }

        $lines = $analyzer->lines();

        foreach ($analyzer->functions() as $function) {
            if (!$function->className()) continue;

            $methodLength = $this->calculateMethodLength($function, $lines);

            if ($methodLength <= $this->maxMethodLength) continue;

            $this->addIssue(
                $relativePath,
                $function->line(),
                sprintf(
                    "Method \"%s\" is too long. (%d lines, max: %d)",
                    $function->classFunctionName(),
                    $methodLength,
                    $this->maxMethodLength
                )
            );
        }
    }

    /**
     * Calculate method length
     *
     * @param \Sentinel\Nodes\FunctionNode $function
     * @param array<string> $lines
     * @return int
     */
    protected function calculateMethodLength(FunctionNode $function, array $lines): int
    {
        $startLine = $function->line() - 1;
        $endLine   = $this->findMethodEndLine($lines, $startLine);

        $count = 0;

        for ($i = $startLine; $i <= $endLine && $i < count($lines); $i++) {
            if (trim($lines[$i]) === "") continue;

            $count++;
        }

        return $count;
    }

    /**
     * Find end line of method
     *
     * @param array<string> $lines
     * @param int $startLine
     * @return int
     */
    protected function findMethodEndLine(array $lines, int $startLine): int
    {
        $braceLevel = 0;
        $foundStart = false;

        for ($i = $startLine; $i < count($lines); $i++) {
            $line = $lines[$i];

            $braceLevel += substr_count($line, "{");
            $braceLevel -= substr_count($line, "}");

            if (str_contains($line, "{")) $foundStart = true;

            if ($foundStart && $braceLevel === 0) return $i;
        }

        return count($lines) - 1;
    }

    /*----------------------------------------*
     * Max Length
     *----------------------------------------*/

    /**
     * Maximum allowed class length
     *
     * @var int
     */
    protected int $maxClassLength = 500;

    /**
     * Maximum allowed method length
     *
     * @var int
     */
    protected int $maxMethodLength = 50;

    /**
     * Get maximum allowed class length
     *
     * @return int
     */
    public function maxClassLength(): int
    {
        return $this->maxClassLength;
    }

    /**
     * Set maximum allowed class length
     *
     * @param int $length
     * @return static
     */
    public function setMaxClassLength(int $length): static
    {
        $this->maxClassLength = $length;

        return $this;
    }

    /**
     * Get maximum allowed method length
     *
     * @return int
     */
    public function maxMethodLength(): int
    {
        return $this->maxMethodLength;
    }

    /**
     * Set maximum allowed method length
     *
     * @param int $length
     * @return static
     */
    public function setMaxMethodLength(int $length): static
    {
        $this->maxMethodLength = $length;

        return $this;
    }
}
