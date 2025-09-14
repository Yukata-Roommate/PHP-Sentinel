<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Type;

use Sentinel\Detectors\Foundation\TypeDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Strict Types Detector
 *
 * @package Sentinel\Detectors\Type
 */
class StrictTypesDetector extends TypeDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Strict Types Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        if ($analyzer->hasStrictTypes()) return;

        $classNotFound    = count($analyzer->classes()) === 0;
        $functionNotFound = count($analyzer->functions()) === 0;
        $propertyNotFound = count($analyzer->properties()) === 0;

        if ($classNotFound && $functionNotFound && $propertyNotFound) return;

        $this->addIssue(
            $relativePath,
            1,
            "Missing declare(strict_types=1) declaration"
        );
    }
}
