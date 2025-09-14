<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Type;

use Sentinel\Detectors\Foundation\TypeDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Return Type Detector
 *
 * @package Sentinel\Detectors\Type
 */
class ReturnTypeDetector extends TypeDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Return Type Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        foreach ($analyzer->functions() as $function) {
            if (in_array($function->name(), ["__construct", "__destruct"], true)) continue;

            if ($function->hasReturnType()) continue;

            $this->addIssue(
                $relativePath,
                $function->line(),
                sprintf(
                    "Missing return type declaration for %s \"%s\".",
                    $function->className() ? "method" : "function",
                    $function->classFunctionName()
                )
            );
        }
    }
}
