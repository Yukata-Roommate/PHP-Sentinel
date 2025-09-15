<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Foundation;

use Sentinel\Detectors\Foundation\Detector;

/**
 * Convention Detector
 *
 * @package Sentinel\Detectors\Foundation
 */
abstract class ConventionDetector extends Detector
{
    /*----------------------------------------*
     * Check
     *----------------------------------------*/

    /**
     * Check if name matches camelCase
     *
     * @param string $name
     * @return bool
     */
    protected function isCamelCase(string $name): bool
    {
        return preg_match("/^[a-z][a-zA-Z0-9]*$/", $name) === 1;
    }

    /**
     * Check if name matches PascalCase
     *
     * @param string $name
     * @return bool
     */
    protected function isPascalCase(string $name): bool
    {
        return preg_match("/^[A-Z][a-zA-Z0-9]*$/", $name) === 1;
    }

    /**
     * Check if name matches snake_case
     *
     * @param string $name
     * @return bool
     */
    protected function isSnakeCase(string $name): bool
    {
        return preg_match("/^[a-z][a-z0-9_]*$/", $name) === 1;
    }

    /**
     * Check if name matches CONSTANT_CASE
     *
     * @param string $name
     * @return bool
     */
    protected function isConstantCase(string $name): bool
    {
        return preg_match("/^[A-Z][A-Z0-9_]*$/", $name) === 1;
    }

    /*----------------------------------------*
     * Convert
     *----------------------------------------*/

    /**
     * Convert camelCase/PascalCase to snake_case
     *
     * @param string $name
     * @return string
     */
    protected function toSnakeCase(string $name): string
    {
        $name = preg_replace("/([A-Z])/", "_$1", $name);

        $name = ltrim($name, "_");

        return strtolower($name);
    }

    /**
     * Convert to CONSTANT_CASE
     *
     * @param string $name
     * @return string
     */
    protected function toConstantCase(string $name): string
    {
        return strtoupper($this->toSnakeCase($name));
    }
}
