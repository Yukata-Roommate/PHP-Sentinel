<?php

declare(strict_types=1);

namespace Sentinel\Exceptions;

use Sentinel\Exceptions\SentinelException;

/**
 * Out of Range Line Exception
 *
 * @package Sentinel\Exceptions
 */
class OutOfRangeLineException extends SentinelException
{
    /**
     * File path
     *
     * @var string
     */
    protected string $filePath;

    /**
     * Line number
     *
     * @var int
     */
    protected int $line;

    /**
     * Constructor
     *
     * @param string $filePath
     * @param int $line
     */
    public function __construct(string $filePath, int $line)
    {
        $this->filePath = $filePath;
        $this->line     = $line;

        parent::__construct("Line {$line} is out of range in file: {$filePath}");
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function filePath(): string
    {
        return $this->filePath;
    }

    /**
     * Get line number
     *
     * @return int
     */
    public function line(): int
    {
        return $this->line;
    }
}
