<?php

declare(strict_types=1);

namespace Sentinel\Nodes\Leaves;

use Sentinel\Nodes\Leaves\PHPDocTag;

/**
 * PHP Doc Var Tag
 *
 * @package Sentinel\Nodes\Leaves
 */
class VarTag extends PHPDocTag
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $type
     * @param string|null $name
     * @param string|null $description
     */
    public function __construct(string $type, string|null $name = null, string|null $description = null)
    {
        $this->type  = $type;
        $this->name  = $name ? ltrim($name, "$") : null;
        $this->types = $this->parseType($type);

        parent::__construct($description);
    }

    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * Name
     *
     * @var string|null
     */
    protected string|null $name;

    /**
     * Get name
     *
     * @return string|null
     */
    public function name(): string|null
    {
        return $this->name;
    }

    /**
     * Get variable name
     *
     * @return string|null
     */
    public function variableName(): string|null
    {
        return $this->name !== null ? "$" . $this->name : null;
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
