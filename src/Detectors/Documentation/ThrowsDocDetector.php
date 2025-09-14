<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Documentation;

use Sentinel\Detectors\Foundation\DocumentationDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Throws Doc Detector
 *
 * @package Sentinel\Detectors\Documentation
 */
class ThrowsDocDetector extends DocumentationDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Throws Doc Detector";
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

        foreach ($analyzer->functions() as $function) {
            if (!$function->hasPHPDoc()) continue;

            $phpDoc = $function->phpDoc();

            $exceptions = $this->findThrownExceptions($lines, $function->line());

            if (empty($exceptions)) continue;

            foreach ($exceptions as $exception) {
                if ($phpDoc->hasThrows($exception)) continue;

                $this->addIssue(
                    $relativePath,
                    $phpDoc->start(),
                    sprintf(
                        "Missing @throws tag for \"%s\" in %s \"%s\".",
                        $exception,
                        $function->className() ? "method" : "function",
                        $function->classFunctionName()
                    )
                );
            }
        }
    }

    /**
     * Find exceptions thrown in function body
     *
     * @param array<string> $lines
     * @param int $startLine
     * @return array<string>
     */
    protected function findThrownExceptions(array $lines, int $startLine): array
    {
        $exceptions = [];

        $braceLevel = 0;
        $inFunction = false;

        for ($i = $startLine - 1; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (str_contains($line, "{")) {
                $braceLevel += substr_count($line, "{");

                $inFunction = true;
            }

            if (str_contains($line, "}")) {
                $braceLevel -= substr_count($line, "}");

                if ($inFunction && $braceLevel === 0) break;
            }

            if ($inFunction && preg_match("/throw\s+new\s+([A-Z][a-zA-Z0-9_\\\\]*)/", $line, $matches)) {
                $exception = $this->getShortName($matches[1]);

                if (!in_array($exception, $exceptions, true)) $exceptions[] = $exception;
            }
        }

        return $exceptions;
    }
}
