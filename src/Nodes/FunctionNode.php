<?php

declare(strict_types=1);

namespace Sentinel\Nodes;

use Sentinel\Nodes\PHPDocNode;
use Sentinel\Nodes\Leaves\Parameter;

/**
 * Function Node
 *
 * @package Sentinel\Nodes
 */
class FunctionNode
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
     * @param array<string, \Sentinel\Nodes\Leaves\Parameter> $parameters
     */
    public function __construct(int $line, string $name, string $visibility, bool $isStatic, array $parameters)
    {
        $this->line       = $line;
        $this->name       = $name;
        $this->visibility = $visibility;
        $this->isStatic   = $isStatic;
        $this->parameters = $parameters;
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
     * Parameter
     *----------------------------------------*/

    /**
     * Parameters
     *
     * @var array<string, \Sentinel\Nodes\Leaves\Parameter>
     */
    protected array $parameters;

    /**
     * Get parameters
     *
     * @return array<string, \Sentinel\Nodes\Leaves\Parameter>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get parameter by name
     *
     * @param string $name
     * @return \Sentinel\Nodes\Leaves\Parameter|null
     */
    public function parameter(string $name): Parameter|null
    {
        return $this->parameters[$name] ?? null;
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
     * @param string|null $className
     * @return static
     */
    public function setClassName(string|null $className): static
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get class function name
     *
     * @return string
     */
    public function classFunctionName(): string
    {
        return ($this->className !== null ? $this->className . "::" : "") . $this->name;
    }

    /*----------------------------------------*
     * Return Type
     *----------------------------------------*/

    /**
     * Return type
     *
     * @var string|null
     */
    protected string|null $returnType = null;

    /**
     * Whether has return type
     *
     * @var bool
     */
    protected bool $hasReturnType = false;

    /**
     * Get return type
     *
     * @return string|null
     */
    public function returnType(): string|null
    {
        return $this->returnType;
    }

    /**
     * Set return type
     *
     * @param string|null $returnType
     * @return static
     */
    public function setReturnType(string|null $returnType): static
    {
        $this->returnType    = $returnType;
        $this->hasReturnType = true;

        return $this;
    }

    /**
     * Check if has return type
     *
     * @return bool
     */
    public function hasReturnType(): bool
    {
        return $this->hasReturnType;
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
     * Get PHPDoc node
     *
     * @return \Sentinel\Nodes\PHPDocNode|null
     */
    public function phpDoc(): PHPDocNode|null
    {
        return $this->phpDoc;
    }

    /**
     * Set PHPDoc node
     *
     * @param \Sentinel\Nodes\PHPDocNode|null $phpDoc
     * @return static
     */
    public function setPhpDoc(PHPDocNode|null $phpDoc): static
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
