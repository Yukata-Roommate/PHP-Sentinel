<?php

declare(strict_types=1);

namespace Sentinel\Exceptions;

use Sentinel\Exceptions\SentinelException;

/**
 * Directory Not Found Exception
 *
 * @package Sentinel\Exceptions
 */
class DirectoryNotFoundException extends SentinelException
{
    /**
     * Directory path
     *
     * @var string
     */
    protected string $directoryPath;

    /**
     * Constructor
     *
     * @param string $directoryPath
     */
    public function __construct(string $directoryPath)
    {
        $this->directoryPath = $directoryPath;

        parent::__construct("Directory not found: \"{$directoryPath}\"");
    }

    /**
     * Get directory path
     *
     * @return string
     */
    public function directoryPath(): string
    {
        return $this->directoryPath;
    }
}
