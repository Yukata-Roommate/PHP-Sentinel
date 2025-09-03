<?php

declare(strict_types=1);

namespace Sentinel\Contracts;

/**
 * Detector Contract
 *
 * @package Sentinel\Contracts
 */
interface Detector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * Get detector name
     *
     * @return string
     */
    public function name(): string;
    
    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * Detect issues in directory
     *
     * @param string $directory
     * @return bool
     */
    public function detect(string $directory): bool;
    
    /*----------------------------------------*
     * Statistics
     *----------------------------------------*/

    /**
     * Get detected issues
     *
     * @return array<\Sentinel\Contracts\Issue>
     */
    public function issues(): array;

    /**
     * Get number of detected files
     *
     * @return int
     */
    public function files(): int;
}
