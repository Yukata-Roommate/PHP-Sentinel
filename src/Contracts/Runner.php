<?php

declare(strict_types=1);

namespace Sentinel\Contracts;

use Sentinel\Contracts\Detector;

/**
 * Runner Contract
 *
 * @package Sentinel\Contracts
 */
interface Runner
{
    /*----------------------------------------*
     * Detectors
     *----------------------------------------*/

    /**
     * Add detector
     *
     * @param \Sentinel\Contracts\Detector $detector
     * @return static
     */
    public function addDetector(Detector $detector): static;

    /**
     * Get detectors
     *
     * @return array<\Sentinel\Contracts\Detector>
     */
    public function detectors(): array;

    /*----------------------------------------*
     * Run
     *----------------------------------------*/

    /**
     * Run all detectors
     *
     * @param string $directory
     * @return bool
     */
    public function run(string $directory): bool;

    /*----------------------------------------*
     * Results
     *----------------------------------------*/

    /**
     * Get all issues from all detectors
     *
     * @return array<\Sentinel\Contracts\Issue>
     */
    public function issues(): array;

    /**
     * Get total files checked
     *
     * @return int
     */
    public function totalFiles(): int;

    /**
     * Check if all passed
     *
     * @return bool
     */
    public function passed(): bool;
}
