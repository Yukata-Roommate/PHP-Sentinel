<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Type;

use Sentinel\Detectors\Foundation\TypeDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Parameter Type Detector
 *
 * @package Sentinel\Detectors\Type
 */
class ParameterTypeDetector extends TypeDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Parameter Type Detector";
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
            foreach ($function->parameters() as $parameter) {
                if ($parameter->hasType()) continue;

                $this->addIssue(
                    $relativePath,
                    $function->line(),
                    sprintf(
                        "Missing type declaration for parameter \"%s\" in %s \"%s\".",
                        $parameter->variableName(),
                        $function->className() ? "method" : "function",
                        $function->classFunctionName()
                    )
                );
            }
        }
    }
}
