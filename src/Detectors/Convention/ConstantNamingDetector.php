<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Convention;

use Sentinel\Detectors\Foundation\ConventionDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Constant Naming Detector
 *
 * @package Sentinel\Detectors\Convention
 */
class ConstantNamingDetector extends ConventionDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Constant Naming Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        $lines = $analyzer->lines();

        foreach ($lines as $lineNumber => $line) {
            if (preg_match("/\bconst\s+([A-Za-z_][A-Za-z0-9_]*)\s*=/", $line, $matches)) {
                $constantName = $matches[1];

                if ($this->isConstantCase($constantName)) continue;

                $this->addIssue(
                    $relativePath,
                    $lineNumber + 1,
                    sprintf(
                        "Constant \"%s\" should be in CONSTANT_CASE.",
                        $constantName
                    )
                );
            }

            if (preg_match("/\bdefine\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/", $line, $matches)) {
                $constantName = $matches[1];

                if (str_contains($constantName, "\\")) continue;

                if ($this->isConstantCase($constantName)) continue;

                $this->addIssue(
                    $relativePath,
                    $lineNumber + 1,
                    sprintf(
                        "Constant \"%s\" should be in CONSTANT_CASE.",
                        $constantName
                    )
                );
            }
        }
    }
}
