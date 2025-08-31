<?php

declare(strict_types=1);

namespace Sentinel\Exceptions;

use Sentinel\Exceptions\SentinelException;

/**
 * File Not Found Exception
 *
 * @package Sentinel\Exceptions
 */
class FileNotFoundException extends SentinelException
{
    /**
     * File filePath
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

        parent::__construct("File not found: \"{$filePath}\"");
    }

    /**
     * Get file filePath
     *
     * @return string
     */
    public function filePath(): string
    {
        return $this->filePath;
    }
}
