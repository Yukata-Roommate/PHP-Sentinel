<?php

declare(strict_types=1);

namespace Sentinel\Nodes\Leaves;

use Sentinel\Nodes\Leaves\PHPDocTag;

/**
 * PHP Doc Return Tag
 *
 * @package Sentinel\Nodes\Leaves
 */
class ReturnTag extends PHPDocTag
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $type
     * @param string|null $description
     */
    public function __construct(string $type, string|null $description)
    {
        $this->type  = $type;
        $this->types = $this->parseType($type);

        parent::__construct($description);
    }

    /*----------------------------------------*
     * Type
     *----------------------------------------*/

    /**
     * Type
     *
     * @var string
     */
    protected string $type;

    /**
     * Get type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /*----------------------------------------*
     * Types
     *----------------------------------------*/

    /**
     * Types
     *
     * @var array<int, string>
     */
    protected array $types;

    /**
     * Get types
     *
     * @return array<int, string>
     */
    public function types(): array
    {
        return $this->types;
    }

    /**
     * Check if type is nullable
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->isNullableType($this->types);
    }
}
