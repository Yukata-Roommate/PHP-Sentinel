<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Documentation;

use Sentinel\Detectors\Foundation\DocumentationDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Missing PHPDoc Detector
 *
 * @package Sentinel\Detectors\Documentation
 */
class MissingPHPDocDetector extends DocumentationDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Missing PHPDoc Detector";
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
            if ($class->hasPHPDoc()) continue;

            $this->addIssue(
                $relativePath,
                $class->start(),
                sprintf("Missing PHPDoc for class \"%s\".", $class->name())
            );
        }

        foreach ($analyzer->functions() as $function) {
            if ($function->hasPHPDoc()) continue;

            $this->addIssue(
                $relativePath,
                $function->line(),
                sprintf(
                    "Missing PHPDoc for %s \"%s\".",
                    $function->className() ? "method" : "function",
                    $function->classFunctionName()
                )
            );
        }

        foreach ($analyzer->properties() as $property) {
            if ($property->hasPHPDoc()) continue;

            $this->addIssue(
                $relativePath,
                $property->line(),
                sprintf(
                    "Missing PHPDoc for property \"%s\".",
                    $property->classPropertyName()
                )
            );
        }
    }
}
