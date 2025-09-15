<?php

declare(strict_types=1);

namespace Sentinel\Nodes\Leaves;

/**
 * Parameter
 *
 * @package Sentinel\Nodes\Leaves
 */
class Parameter
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $name
     * @param bool $isNullable
     * @param string|null $type
     * @param string|null $defaultValue
     * @param bool $isVariadic
     * @param bool $isReference
     * @param bool $isPromoted
     */
    public function __construct(string $name, bool $isNullable, string|null $type, string|null $defaultValue, bool $isVariadic, bool $isReference, bool $isPromoted)
    {
        $this->name         = ltrim($name, "$");
        $this->isNullable   = $isNullable;
        $this->type         = $type;
        $this->defaultValue = $defaultValue;
        $this->isVariadic   = $isVariadic;
        $this->isReference  = $isReference;
        $this->isPromoted   = $isPromoted;
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
     * @var string|null
     */
    protected string|null $type;

    /**
     * Get type
     *
     * @return string|null
     */
    public function type(): string|null
    {
        return $this->type;
    }

    /**
     * Check if has type
     *
     * @return bool
     */
    public function hasType(): bool
    {
        return $this->type !== null;
    }

    /**
     * Check if type is union type
     *
     * @return bool
     */
    public function isUnionType(): bool
    {
        return $this->hasType() && str_contains($this->type, "|");
    }

    /**
     * Check if type is intersection type
     *
     * @return bool
     */
    public function isIntersectionType(): bool
    {
        return $this->hasType() && str_contains($this->type, "&");
    }

    /**
     * Check if type is Disjunctive Normal Form type
     *
     * @return bool
     */
    public function isDNFType(): bool
    {
        return $this->hasType() && preg_match("/\([^()]+[|&][^()]+\)/", $this->type) === 1;
    }

    /*----------------------------------------*
     * Default
     *----------------------------------------*/

    /**
     * Default value
     *
     * @var string|null
     */
    protected $defaultValue;

    /**
     * Get default value
     *
     * @return string|null
     */
    public function defaultValue(): string|null
    {
        return $this->defaultValue;
    }

    /**
     * Whether has default value
     *
     * @return bool
     */
    public function hasDefault(): bool
    {
        return $this->defaultValue !== null;
    }

    /*----------------------------------------*
     * Nullable
     *----------------------------------------*/

    /**
     * Whether is nullable
     *
     * @var bool
     */
    protected bool $isNullable;

    /**
     * Check if is nullable
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /*----------------------------------------*
     * Variadic
     *----------------------------------------*/

    /**
     * Whether is variadic
     *
     * @var bool
     */
    protected bool $isVariadic;

    /**
     * Check if is variadic
     *
     * @return bool
     */
    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    /*----------------------------------------*
     * Reference
     *----------------------------------------*/

    /**
     * Whether is passed by reference
     *
     * @var bool
     */
    protected bool $isReference;

    /**
     * Check if is passed by reference
     *
     * @return bool
     */
    public function isReference(): bool
    {
        return $this->isReference;
    }

    /*----------------------------------------*
     * Promoted
     *----------------------------------------*/

    /**
     * Whether is promoted property
     *
     * @var bool
     */
    protected bool $isPromoted;

    /**
     * Check if is promoted property
     *
     * @return bool
     */
    public function isPromoted(): bool
    {
        return $this->isPromoted;
    }
}
