<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Structure;

use Sentinel\Detectors\Foundation\StructureDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Unused Imports Detector
 *
 * @package Sentinel\Detectors\Structure
 */
class UnusedImportsDetector extends StructureDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Unused Imports Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        $uses = $analyzer->uses();

        if (empty($uses)) return;

        $lines = $analyzer->lines();

        foreach ($uses as $use) {
            $shortName = $use->effectiveName();

            $startLine = $this->findCodeStartLine($lines);

            if ($this->isNameUsedInCode($shortName, $lines, $startLine)) continue;

            $this->addIssue(
                $relativePath,
                $use->line(),
                sprintf("Unused import \"%s\".", $use->fullName())
            );
        }
    }

    /**
     * Find line where actual code starts
     *
     * @param array<string> $lines
     * @return int
     */
    protected function findCodeStartLine(array $lines): int
    {
        $inUseBlock = false;

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if (empty($line) || str_starts_with($line, "//") || str_starts_with($line, "/*")) continue;

            if (str_starts_with($line, "use ")) {
                $inUseBlock = true;

                continue;
            }

            if ($inUseBlock && !str_starts_with($line, "use ")) {
                return $i;
            }
        }

        return 0;
    }

    /**
     * Check if import is used in code
     *
     * @param string $shortName
     * @param array<string> $lines
     * @param int $startLine
     * @return bool
     */
    protected function isNameUsedInCode(string $shortName, array $lines, int $startLine): bool
    {
        for ($i = $startLine; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (preg_match("/^\s*(use\s+|\/\/|\/\*|\*)/", $line)) continue;

            if (preg_match("/\b" . preg_quote($shortName, "/") . "\b/", $line)) return true;
        }

        return false;
    }
}
