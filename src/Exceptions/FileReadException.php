<?php

declare(strict_types=1);

namespace Sentinel\Exceptions;

use Sentinel\Exceptions\SentinelException;

/**
 * File Read Exception
 *
 * @package Sentinel\Exceptions
 */
class FileReadException extends SentinelException
{
    /**
     * File path
     *
     * @var string
     */
    protected string $filePath;

    /**
     * Constructor
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;

        parent::__construct("Failed to read file: {$filePath}");
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
}
