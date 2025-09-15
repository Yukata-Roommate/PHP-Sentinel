<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Convention;

use Sentinel\Detectors\Foundation\ConventionDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Naming Convention Detector
 *
 * @package Sentinel\Detectors\Convention
 */
class NamingConventionDetector extends ConventionDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Naming Convention Detector";
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
            if ($this->isPascalCase($class->name())) continue;

            $this->addIssue(
                $relativePath,
                $class->start(),
                sprintf(
                    "Class \"%s\" should be in PascalCase.",
                    $class->name()
                )
            );
        }

        foreach ($analyzer->functions() as $function) {
            $name = $function->name();

            if (str_starts_with($name, "__")) continue;

            if ($this->isCamelCase($name)) continue;

            $this->addIssue(
                $relativePath,
                $function->line(),
                sprintf(
                    "%s \"%s\" should be in camelCase.",
                    $function->className() ? "Method" : "Function",
                    $function->classFunctionName()
                )
            );
        }

        foreach ($analyzer->properties() as $property) {
            $name = $property->name();

            if ($this->isCamelCase($name)) continue;

            $this->addIssue(
                $relativePath,
                $property->line(),
                sprintf(
                    "Property \"%s\" should be in camelCase.",
                    $property->classPropertyName()
                )
            );
        }
    }
}
