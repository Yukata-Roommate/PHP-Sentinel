<?php

declare(strict_types=1);

namespace Sentinel\Exceptions;

use Sentinel\Exceptions\SentinelException;

/**
 * Throws Tag Not Found Exception
 *
 * @package Sentinel\Exceptions
 */
class ThrowsTagNotFoundException extends SentinelException
{
    /**
     * Throws name
     *
     * @var string
     */
    protected string $name;

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        parent::__construct("Throws tag not found in phpdoc: \"{$name}\"");
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
