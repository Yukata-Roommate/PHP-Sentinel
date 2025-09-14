<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Type;

use Sentinel\Detectors\Foundation\TypeDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Property Type Detector
 *
 * @package Sentinel\Detectors\Type
 */
class PropertyTypeDetector extends TypeDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Property Type Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        foreach ($analyzer->properties() as $property) {
            if ($property->hasType()) continue;

            $this->addIssue(
                $relativePath,
                $property->line(),
                sprintf(
                    "Missing type declaration for %s property \"%s\" in class \"%s\".",
                    $property->visibility(),
                    $property->variableName(),
                    $property->className() ?: "unknown"
                )
            );
        }
    }
}
