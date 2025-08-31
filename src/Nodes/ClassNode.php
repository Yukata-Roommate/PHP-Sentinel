<?php

declare(strict_types=1);

namespace Sentinel\Nodes;

use Sentinel\Nodes\PHPDocNode;

/**
 * Class Node
 *
 * @package Sentinel\Nodes
 */
class ClassNode
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $name
     * @param int $startLine
     * @param string|null $modifier
     */
    public function __construct(string $name, int $startLine, string|null $modifier)
    {
        $this->name     = $name;
        $this->start    = $startLine;
        $this->modifier = $modifier;
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
     * Line
     *----------------------------------------*/

    /**
     * Start line number
     *
     * @var int
     */
    protected int $start;

    /**
     * End line number
     *
     * @var int|null
     */
    protected int|null $end = null;

    /**
     * Get start line number
     *
     * @return int
     */
    public function start(): int
    {
        return $this->start;
    }

    /**
     * Get end line number
     *
     * @return int|null
     */
    public function end(): int|null
    {
        return $this->end;
    }

    /**
     * Set end line number
     *
     * @param int|null $end
     * @return static
     */
    public function setEnd(int|null $end): static
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get line count
     *
     * @return int|null
     */
    public function lineCount(): int|null
    {
        if ($this->end === null) return null;

        return $this->end - $this->start + 1;
    }

    /*----------------------------------------*
     * Modifier
     *----------------------------------------*/

    /**
     * Modifier
     *
     * @var string|null
     */
    protected string|null $modifier;

    /**
     * Get modifier
     *
     * @return string|null
     */
    public function modifier(): string|null
    {
        return $this->modifier;
    }

    /**
     * Check if is abstract
     *
     * @return bool
     */
    public function isAbstract(): bool
    {
        return $this->modifier === "abstract";
    }

    /**
     * Check if is final
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return $this->modifier === "final";
    }

    /*----------------------------------------*
     * Namespace
     *----------------------------------------*/

    /**
     * Namespace
     *
     * @var string|null
     */
    protected string|null $namespace = null;

    /**
     * Get namespace
     *
     * @return string|null
     */
    public function namespace(): string|null
    {
        return $this->namespace;
    }

    /**
     * Set namespace
     *
     * @param string|null $namespace
     * @return static
     */
    public function setNamespace(string|null $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get fully qualified name
     *
     * @return string
     */
    public function fullyQualifiedName(): string
    {
        return ($this->namespace ? $this->namespace . "\\" : "") . $this->name;
    }

    /*----------------------------------------*
     * Extends
     *----------------------------------------*/

    /**
     * Extends class
     *
     * @var string|null
     */
    protected string|null $extends = null;

    /**
     * Get extends class
     *
     * @return string|null
     */
    public function extends(): string|null
    {
        return $this->extends;
    }

    /**
     * Set extends class
     *
     * @param string|null $extends
     * @return static
     */
    public function setExtends(string|null $extends): static
    {
        $this->extends = $extends;

        return $this;
    }

    /*----------------------------------------*
     * Implements
     *----------------------------------------*/

    /**
     * Implements interfaces
     *
     * @var array<string>
     */
    protected array $implements = [];

    /**
     * Get implements interfaces
     *
     * @return array<string>
     */
    public function implements(): array
    {
        return $this->implements;
    }

    /**
     * Add implements interface
     *
     * @param string $interface
     * @return static
     */
    public function addImplements(string $interface): static
    {
        if (in_array($interface, $this->implements, true)) return $this;

        $this->implements[] = $interface;

        return $this;
    }

    /*----------------------------------------*
     * Use
     *----------------------------------------*/

    /**
     * Uses traits
     *
     * @var array<string>
     */
    protected array $uses = [];

    /**
     * Get uses traits
     *
     * @return array<string>
     */
    public function uses(): array
    {
        return $this->uses;
    }

    /**
     * Add uses trait
     *
     * @param string $trait
     * @return static
     */
    public function addUses(string $trait): static
    {
        if (in_array($trait, $this->uses, true)) return $this;

        $this->uses[] = $trait;

        return $this;
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
