<?php

declare(strict_types=1);

namespace Sentinel\Nodes\Leaves;

use Sentinel\Nodes\Leaves\PHPDocTag;

/**
 * PHP Doc Param Tag
 *
 * @package Sentinel\Nodes\Leaves
 */
class ParamTag extends PHPDocTag
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $name
     * @param string $type
     * @param string|null $description
     */
    public function __construct(string $name, string $type, string|null $description = null)
    {
        $this->name  = ltrim($name, "$");
        $this->type  = $type;
        $this->types = $this->parseType($type);

        parent::__construct($description);
    }

    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * Name
     *
     * @var string
     */
    protected string $name;

    /**
     * Get name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get variable name
     *
     * @return string
     */
    public function variableName(): string
    {
        return "$" . $this->name;
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

    /**
     * Check if type is variadic
     *
     * @return bool
     */
    public function isVariadic(): bool
    {
        return $this->isVariadicType($this->type);
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
