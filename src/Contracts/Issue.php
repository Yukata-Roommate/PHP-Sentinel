<?php

declare(strict_types=1);

namespace Sentinel\Contracts;

/**
 * Issue Contract
 *
 * @package Sentinel\Contracts
 */
interface Issue extends \Stringable
{
    /**
     * Get file path
     *
     * @return string
     */
    public function file(): string;

    /**
     * Get line number
     *
     * @return int
     */
    public function line(): int;

    /**
     * Get issue message
     *
     * @return string
     */
    public function message(): string;

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
