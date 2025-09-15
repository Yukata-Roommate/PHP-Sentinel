<?php

declare(strict_types=1);

namespace Sentinel\Nodes;

use Sentinel\Nodes\PHPDocNode;

/**
 * Property Node
 *
 * @package Sentinel\Nodes
 */
class PropertyNode
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param int $line
     * @param string $name
     * @param string $visibility
     * @param bool $isStatic
     * @param bool $isReadonly
     */
    public function __construct(int $line, string $name, string $visibility, bool $isStatic, bool $isReadonly)
    {
        $this->line       = $line;
        $this->name       = ltrim($name, "$");
        $this->visibility = $visibility;
        $this->isStatic   = $isStatic;
        $this->isReadonly = $isReadonly;
    }

    /*----------------------------------------*
     * Line
     *----------------------------------------*/

    /**
     * Line number
     *
     * @var int
     */
    protected int $line;

    /**
     * Get line number
     *
     * @return int
     */
    public function line(): int
    {
        return $this->line;
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
     * Visibility
     *----------------------------------------*/

    /**
     * Visibility
     *
     * @var string
     */
    protected string $visibility;

    /**
     * Get visibility
     *
     * @return string
     */
    public function visibility(): string
    {
        return $this->visibility;
    }

    /**
     * Check if visibility is public
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->visibility === "public";
    }

    /**
     * Check if visibility is protected
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return $this->visibility === "protected";
    }

    /**
     * Check if visibility is private
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->visibility === "private";
    }

    /*----------------------------------------*
     * Static
     *----------------------------------------*/

    /**
     * Whether property is static
     *
     * @var bool
     */
    protected bool $isStatic;

    /**
     * Check if property is static
     *
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /*----------------------------------------*
     * Readonly
     *----------------------------------------*/

    /**
     * Whether property is readonly
     *
     * @var bool
     */
    protected bool $isReadonly;

    /**
     * Check if property is readonly
     *
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->isReadonly;
    }

    /*----------------------------------------*
     * Class Name
     *----------------------------------------*/

    /**
     * Class name
     *
     * @var string|null
     */
    protected string|null $className = null;

    /**
     * Get class name
     *
     * @return string|null
     */
    public function className(): string|null
    {
        return $this->className;
    }

    /**
     * Set class name
     *
     * @param string $className
     * @return static
     */
    public function setClassName(string $className): static
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get class property name
     *
     * @return string
     */
    public function classPropertyName(): string
    {
        return ($this->className !== null ? $this->className . "::" : "") . $this->variableName();
    }

    /*----------------------------------------*
     * Type
     *----------------------------------------*/

    /**
     * Type
     *
     * @var string|null
     */
    protected string|null $type = null;

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
     * Set type
     *
     * @param string|null $type
     * @return static
     */
    public function setType(string|null $type): static
    {
        $this->type = $type;

        return $this;
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
     * Check if type is nullable
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        if (!$this->hasType()) return false;

        return str_starts_with($this->type, "?") || str_contains($this->type, "|null");
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
        return $this->hasType() && preg_match('/\([^()]+[|&][^()]+\)/', $this->type) === 1;
    }

    /*----------------------------------------*
     * Default Value
     *----------------------------------------*/

    /**
     * Default value
     *
     * @var string|int|float|bool|null
     */
    protected string|int|float|bool|null $defaultValue = null;

    /**
     * Whether has default value
     *
     * @var bool
     */
    protected bool $hasDefault = false;

    /**
     * Get default value
     *
     * @return string|int|float|bool|null
     */
    public function defaultValue(): string|int|float|bool|null
    {
        return $this->defaultValue;
    }

    /**
     * Set default value
     *
     * @param string|int|float|bool|null $defaultValue
     * @return static
     */
    public function setDefaultValue(string|int|float|bool|null $defaultValue): static
    {
        $this->defaultValue = $defaultValue;
        $this->hasDefault   = true;

        return $this;
    }

    /**
     * Check if has default value
     *
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    /*----------------------------------------*
     * PHPDoc
     *----------------------------------------*/

    /**
     * PHPDoc node
     *
     * @var \Sentinel\Nodes\PHPDocNode|null
     */
    protected PHPDocNode|null $phpDoc = null;

    /**
     * Get PHPDoc Node
     *
     * @return \Sentinel\Nodes\PHPDocNode|null
     */
    public function phpDoc(): PHPDocNode|null
    {
        return $this->phpDoc;
    }

    /**
     * Set PHPDoc Node
     *
     * @param \Sentinel\Nodes\PHPDocNode|null $phpDoc
     * @return static
     */
    public function setPHPDoc(PHPDocNode|null $phpDoc): static
    {
        $this->phpDoc = $phpDoc;

        return $this;
    }

    /**
     * Check if has PHPDoc Node
     *
     * @return bool
     */
    public function hasPHPDoc(): bool
    {
        return $this->phpDoc !== null;
    }
}
