<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Foundation;

use Sentinel\Detectors\Foundation\Detector;

/**
 * Documentation Detector
 *
 * @package Sentinel\Detectors\Foundation
 */
abstract class DocumentationDetector extends Detector
{
    /**
     * Get short class name
     *
     * @param string $className
     * @return string
     */
    protected function getShortName(string $className): string
    {
        $pos = strrpos($className, "\\");

        return $pos !== false ? substr($className, $pos + 1) : $className;
    }
}
