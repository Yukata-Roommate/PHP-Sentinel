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
     * Exclude Directories
     *----------------------------------------*/

    /**
     * Get exclude directories
     *
     * @return array<string>
     */
    public function excludeDirectories(): array;

    /**
     * Set exclude directories
     *
     * @param array<string> $directories
     * @return static
     */
    public function setExcludeDirectories(array $directories): static;

    /**
     * Add exclude directory
     *
     * @param string $directory
     * @return static
     */
    public function addExcludeDirectory(string $directory): static;

    /**
     * Remove exclude directory
     *
     * @param string $directory
     * @return static
     */
    public function removeExcludeDirectory(string $directory): static;

    /*----------------------------------------*
     * Gitignore
     *----------------------------------------*/

    /**
     * Set whether to use gitignore
     *
     * @param bool $useGitignore
     * @return static
     */
    public function setUseGitignore(bool $useGitignore): static;

    /**
     * Set true to use gitignore
     *
     * @return static
     */
    public function useGitignore(): static;

    /**
     * Set false to not use gitignore
     *
     * @return static
     */
    public function notUseGitignore(): static;

    /**
     * Set whether to preload all gitignores
     *
     * @param bool $preloadGitignores
     * @return static
     */
    public function setPreloadGitignores(bool $preloadGitignores): static;

    /**
     * Set true to preload all gitignores
     *
     * @return static
     */
    public function preloadGitignores(): static;

    /**
     * Set false to not preload all gitignores
     *
     * @return static
     */
    public function notPreloadGitignores(): static;

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
