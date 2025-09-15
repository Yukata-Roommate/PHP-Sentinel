<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Structure;

use Sentinel\Detectors\Foundation\StructureDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Namespace Detector
 *
 * @package Sentinel\Detectors\Structure
 */
class NamespaceDetector extends StructureDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Namespace Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        $classes = $analyzer->classes();

        if (empty($classes)) return;

        $namespace = $analyzer->namespace();

        if ($namespace !== null) return;

        $this->addIssue(
            $relativePath,
            1,
            'Missing namespace declaration'
        );
    }
}
