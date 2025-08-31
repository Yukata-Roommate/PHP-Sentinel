<?php

declare(strict_types=1);

namespace Sentinel\Nodes\Leaves;

use Sentinel\Nodes\Leaves\PHPDocTag;

/**
 * PHP Doc Throws Tag
 *
 * @package Sentinel\Nodes\Leaves
 */
class ThrowsTag extends PHPDocTag
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $exception
     * @param string|null $description
     */
    public function __construct(string $exception, string|null $description = null)
    {
        $this->exception = ltrim($exception, "\\");

        parent::__construct($description);
    }

    /*----------------------------------------*
     * Exception
     *----------------------------------------*/

    /**
     * Exception
     *
     * @var string
     */
    protected string $exception;

    /**
     * Get exception
     *
     * @return string
     */
    public function exception(): string
    {
        return $this->exception;
    }
}
