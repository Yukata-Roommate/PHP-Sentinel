<?php

declare(strict_types=1);

namespace Sentinel\Contracts;

use Sentinel\Contracts\Parser;

/**
 * Analyzer Contract
 *
 * @package Sentinel\Contracts
 */
interface Analyzer extends Parser
{
    /*----------------------------------------*
     * File
     *----------------------------------------*/

    /**
     * Set file path
     *
     * @param string $file
     * @return static
     * @throws \Sentinel\Exceptions\FileNotFoundException
     * @throws \Sentinel\Exceptions\FileReadException
     */
    public function setFile(string $file): static;

    /**
     * Get file path
     *
     * @return string
     */
    public function file(): string;

    /**
     * Get file content
     *
     * @return string
     */
    public function content(): string;

    /**
     * Get file lines
     *
     * @return array<int, string>
     */
    public function lines(): array;

    /**
     * Get file line text
     *
     * @param int $line
     * @return string
     * @throws \Sentinel\Exceptions\OutOfRangeLineException
     */
    public function line(int $line): string;
}
