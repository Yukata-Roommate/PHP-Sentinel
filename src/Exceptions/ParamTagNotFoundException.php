<?php

declare(strict_types=1);

namespace Sentinel\Exceptions;

use Sentinel\Exceptions\SentinelException;

/**
 * Param Tag Not Found Exception
 *
 * @package Sentinel\Exceptions
 */
class ParamTagNotFoundException extends SentinelException
{
    /**
     * Param name
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

        parent::__construct("Param tag not found in phpdoc: \"{$name}\"");
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
